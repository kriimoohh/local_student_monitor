<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Automated report scheduler and generator.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Report scheduler manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_scheduler {

    /**
     * Report types.
     */
    const REPORT_EXECUTIVE_SUMMARY = 'executive_summary';
    const REPORT_SUPERVISOR_PERFORMANCE = 'supervisor_performance';
    const REPORT_STUDENT_RISK = 'student_risk';
    const REPORT_RETENTION = 'retention';
    const REPORT_COHORT_ANALYSIS = 'cohort_analysis';

    /**
     * Frequency options.
     */
    const FREQ_DAILY = 'daily';
    const FREQ_WEEKLY = 'weekly';
    const FREQ_MONTHLY = 'monthly';
    const FREQ_QUARTERLY = 'quarterly';

    /**
     * Create a new scheduled report.
     *
     * @param string $reporttype Report type
     * @param string $frequency Frequency
     * @param array $recipients Email recipients
     * @param array $parameters Report parameters
     * @param string $format Output format (pdf, csv, html)
     * @return int Schedule ID
     */
    public function create_schedule($reporttype, $frequency, $recipients, $parameters = [], $format = 'pdf') {
        global $DB;

        $schedule = new \stdClass();
        $schedule->report_type = $reporttype;
        $schedule->frequency = $frequency;
        $schedule->recipients = json_encode($recipients);
        $schedule->parameters = json_encode($parameters);
        $schedule->format = $format;
        $schedule->enabled = 1;
        $schedule->last_run = 0;
        $schedule->next_run = $this->calculate_next_run($frequency);
        $schedule->timecreated = time();
        $schedule->timemodified = time();

        return $DB->insert_record('local_sm_report_schedules', $schedule);
    }

    /**
     * Calculate next run time based on frequency.
     *
     * @param string $frequency Frequency
     * @param int $from From timestamp (default = now)
     * @return int Next run timestamp
     */
    protected function calculate_next_run($frequency, $from = null) {
        if ($from === null) {
            $from = time();
        }

        switch ($frequency) {
            case self::FREQ_DAILY:
                // Next day at 6 AM.
                return strtotime('tomorrow 06:00', $from);

            case self::FREQ_WEEKLY:
                // Next Monday at 6 AM.
                return strtotime('next Monday 06:00', $from);

            case self::FREQ_MONTHLY:
                // First day of next month at 6 AM.
                return strtotime('first day of next month 06:00', $from);

            case self::FREQ_QUARTERLY:
                // First day of next quarter at 6 AM.
                $month = date('n', $from);
                $quarter = ceil($month / 3);
                $nextquarter = $quarter + 1;
                if ($nextquarter > 4) {
                    $nextquarter = 1;
                    $year = date('Y', $from) + 1;
                } else {
                    $year = date('Y', $from);
                }
                $month = ($nextquarter - 1) * 3 + 1;
                return mktime(6, 0, 0, $month, 1, $year);

            default:
                return $from + (24 * 3600); // Default to daily.
        }
    }

    /**
     * Process due scheduled reports.
     *
     * @return int Number of reports processed
     */
    public function process_due_reports() {
        global $DB;

        $now = time();
        $processed = 0;

        $schedules = $DB->get_records_select('local_sm_report_schedules',
            'enabled = 1 AND next_run <= :now',
            ['now' => $now]
        );

        foreach ($schedules as $schedule) {
            try {
                $this->generate_and_send_report($schedule);

                // Update schedule.
                $schedule->last_run = $now;
                $schedule->next_run = $this->calculate_next_run($schedule->frequency, $now);
                $schedule->timemodified = $now;

                $DB->update_record('local_sm_report_schedules', $schedule);

                $processed++;
            } catch (\Exception $e) {
                // Log error but continue processing other reports.
                debugging('Error processing scheduled report ID ' . $schedule->id . ': ' . $e->getMessage());
            }
        }

        return $processed;
    }

    /**
     * Generate and send a scheduled report.
     *
     * @param object $schedule Schedule record
     */
    protected function generate_and_send_report($schedule) {
        global $CFG;

        $parameters = json_decode($schedule->parameters, true) ?? [];
        $recipients = json_decode($schedule->recipients, true) ?? [];

        // Generate report data.
        $reportdata = $this->generate_report_data($schedule->report_type, $parameters);

        // Generate file based on format.
        $filepath = $this->generate_report_file($reportdata, $schedule->format, $schedule->report_type);

        // Send email with attachment.
        $this->send_report_email($recipients, $reportdata, $filepath, $schedule->format);

        // Clean up temporary file.
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Generate report data.
     *
     * @param string $reporttype Report type
     * @param array $parameters Parameters
     * @return object Report data
     */
    protected function generate_report_data($reporttype, $parameters) {
        $biengine = new bi_analytics_engine();

        $reportdata = new \stdClass();
        $reportdata->report_type = $reporttype;
        $reportdata->generated_at = time();
        $reportdata->parameters = $parameters;

        switch ($reporttype) {
            case self::REPORT_EXECUTIVE_SUMMARY:
                $reportdata->data = $biengine->generate_executive_summary();
                break;

            case self::REPORT_SUPERVISOR_PERFORMANCE:
                $reportdata->data = $biengine->get_supervisor_performance();
                break;

            case self::REPORT_STUDENT_RISK:
                $reportdata->data = $this->generate_student_risk_report($parameters);
                break;

            case self::REPORT_RETENTION:
                $days = $parameters['days'] ?? 90;
                $reportdata->data = $biengine->get_retention_analytics($days);
                break;

            case self::REPORT_COHORT_ANALYSIS:
                $groupby = $parameters['groupby'] ?? 'course';
                $reportdata->data = $biengine->get_cohort_analysis($groupby);
                break;

            default:
                throw new \moodle_exception('invalidreporttype', 'local_student_monitor');
        }

        return $reportdata;
    }

    /**
     * Generate student risk report.
     *
     * @param array $parameters Parameters
     * @return object Report data
     */
    protected function generate_student_risk_report($parameters) {
        global $DB;

        $risklevel = $parameters['risk_level'] ?? null;
        $limit = $parameters['limit'] ?? 100;

        $sql = "SELECT
                    st.userid,
                    u.firstname,
                    u.lastname,
                    u.email,
                    st.risk_level,
                    st.risk_score,
                    st.inactivity_days,
                    st.missing_assignments,
                    st.notification_count,
                    st.last_login_time,
                    st.assigned_to
                FROM {local_sm_student_tracking} st
                JOIN {user} u ON u.id = st.userid
                WHERE 1=1";

        $params = [];

        if ($risklevel) {
            $sql .= " AND st.risk_level = :risklevel";
            $params['risklevel'] = $risklevel;
        }

        $sql .= " ORDER BY st.risk_score DESC, st.inactivity_days DESC";

        return (object)[
            'students' => $DB->get_records_sql($sql, $params, 0, $limit),
            'filter' => $risklevel,
            'count' => count($DB->get_records_sql($sql, $params))
        ];
    }

    /**
     * Generate report file.
     *
     * @param object $reportdata Report data
     * @param string $format Format (pdf, csv, html)
     * @param string $reporttype Report type
     * @return string File path
     */
    protected function generate_report_file($reportdata, $format, $reporttype) {
        global $CFG;

        $filename = 'report_' . $reporttype . '_' . date('Y-m-d') . '.' . $format;
        $filepath = $CFG->tempdir . '/' . $filename;

        switch ($format) {
            case 'pdf':
                $this->generate_pdf_report($reportdata, $filepath);
                break;

            case 'csv':
                $this->generate_csv_report($reportdata, $filepath);
                break;

            case 'html':
                $this->generate_html_report($reportdata, $filepath);
                break;

            default:
                throw new \moodle_exception('invalidformat', 'local_student_monitor');
        }

        return $filepath;
    }

    /**
     * Generate PDF report.
     *
     * @param object $reportdata Report data
     * @param string $filepath Output path
     */
    protected function generate_pdf_report($reportdata, $filepath) {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $pdf = new \pdf();
        $pdf->SetTitle(get_string('report_' . $reportdata->report_type, 'local_student_monitor'));
        $pdf->AddPage();

        // Title.
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, get_string('report_' . $reportdata->report_type, 'local_student_monitor'), 0, 1, 'C');

        // Generated date.
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, get_string('generatedon', 'local_student_monitor') . ': ' .
                   userdate($reportdata->generated_at), 0, 1, 'C');
        $pdf->Ln(5);

        // Content based on report type.
        $pdf->SetFont('helvetica', '', 11);
        $content = $this->format_report_content($reportdata);
        $pdf->MultiCell(0, 5, $content);

        $pdf->Output($filepath, 'F');
    }

    /**
     * Generate CSV report.
     *
     * @param object $reportdata Report data
     * @param string $filepath Output path
     */
    protected function generate_csv_report($reportdata, $filepath) {
        $fp = fopen($filepath, 'w');

        // Header.
        fputcsv($fp, ['Report: ' . $reportdata->report_type]);
        fputcsv($fp, ['Generated: ' . userdate($reportdata->generated_at)]);
        fputcsv($fp, []);

        // Data based on report type.
        $this->write_csv_data($fp, $reportdata);

        fclose($fp);
    }

    /**
     * Generate HTML report.
     *
     * @param object $reportdata Report data
     * @param string $filepath Output path
     */
    protected function generate_html_report($reportdata, $filepath) {
        $html = '<html><head><title>' . get_string('report_' . $reportdata->report_type, 'local_student_monitor') . '</title>';
        $html .= '<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
        </style></head><body>';

        $html .= '<h1>' . get_string('report_' . $reportdata->report_type, 'local_student_monitor') . '</h1>';
        $html .= '<p>Generated: ' . userdate($reportdata->generated_at) . '</p>';

        $html .= $this->format_html_content($reportdata);

        $html .= '</body></html>';

        file_put_contents($filepath, $html);
    }

    /**
     * Format report content for PDF.
     *
     * @param object $reportdata Report data
     * @return string Formatted content
     */
    protected function format_report_content($reportdata) {
        $content = '';

        switch ($reportdata->report_type) {
            case self::REPORT_EXECUTIVE_SUMMARY:
                $data = $reportdata->data;
                $content .= "Total Students: {$data->overview->total_students}\n";
                $content .= "Needs Intervention: {$data->overview->needs_intervention}\n";
                $content .= "Success Rate: {$data->overview->success_rate}%\n";
                $content .= "Avg Response Time: {$data->overview->avg_response_time}h\n";
                break;

            default:
                $content = print_r($reportdata->data, true);
        }

        return $content;
    }

    /**
     * Write CSV data.
     *
     * @param resource $fp File pointer
     * @param object $reportdata Report data
     */
    protected function write_csv_data($fp, $reportdata) {
        switch ($reportdata->report_type) {
            case self::REPORT_STUDENT_RISK:
                fputcsv($fp, ['Name', 'Email', 'Risk Level', 'Risk Score', 'Inactivity Days', 'Missing Assignments']);
                foreach ($reportdata->data->students as $student) {
                    fputcsv($fp, [
                        fullname($student),
                        $student->email,
                        $student->risk_level,
                        $student->risk_score,
                        $student->inactivity_days,
                        $student->missing_assignments
                    ]);
                }
                break;
        }
    }

    /**
     * Format HTML content.
     *
     * @param object $reportdata Report data
     * @return string HTML content
     */
    protected function format_html_content($reportdata) {
        $html = '';

        switch ($reportdata->report_type) {
            case self::REPORT_EXECUTIVE_SUMMARY:
                $data = $reportdata->data;
                $html .= '<h2>Overview</h2>';
                $html .= '<table>';
                $html .= '<tr><th>Metric</th><th>Value</th></tr>';
                $html .= "<tr><td>Total Students</td><td>{$data->overview->total_students}</td></tr>";
                $html .= "<tr><td>Needs Intervention</td><td>{$data->overview->needs_intervention}</td></tr>";
                $html .= "<tr><td>Success Rate</td><td>{$data->overview->success_rate}%</td></tr>";
                $html .= "<tr><td>Avg Response Time</td><td>{$data->overview->avg_response_time}h</td></tr>";
                $html .= '</table>';
                break;
        }

        return $html;
    }

    /**
     * Send report email.
     *
     * @param array $recipients Email recipients
     * @param object $reportdata Report data
     * @param string $filepath Attachment path
     * @param string $format Format
     */
    protected function send_report_email($recipients, $reportdata, $filepath, $format) {
        global $CFG;

        $subject = get_string('scheduledreport', 'local_student_monitor') . ': ' .
                   get_string('report_' . $reportdata->report_type, 'local_student_monitor');

        $message = get_string('scheduledreportbody', 'local_student_monitor', [
            'reporttype' => get_string('report_' . $reportdata->report_type, 'local_student_monitor'),
            'date' => userdate($reportdata->generated_at)
        ]);

        foreach ($recipients as $email) {
            email_to_user(
                (object)['email' => $email, 'firstname' => '', 'lastname' => '', 'mailformat' => 1],
                get_admin(),
                $subject,
                $message,
                '',
                $filepath,
                basename($filepath)
            );
        }
    }

    /**
     * Get all scheduled reports.
     *
     * @return array Schedules
     */
    public function get_all_schedules() {
        global $DB;

        return $DB->get_records('local_sm_report_schedules', null, 'timecreated DESC');
    }

    /**
     * Delete a schedule.
     *
     * @param int $scheduleid Schedule ID
     * @return bool Success
     */
    public function delete_schedule($scheduleid) {
        global $DB;

        return $DB->delete_records('local_sm_report_schedules', ['id' => $scheduleid]);
    }

    /**
     * Toggle schedule enabled status.
     *
     * @param int $scheduleid Schedule ID
     * @return bool Success
     */
    public function toggle_schedule($scheduleid) {
        global $DB;

        $schedule = $DB->get_record('local_sm_report_schedules', ['id' => $scheduleid], '*', MUST_EXIST);
        $schedule->enabled = $schedule->enabled ? 0 : 1;
        $schedule->timemodified = time();

        return $DB->update_record('local_sm_report_schedules', $schedule);
    }
}
