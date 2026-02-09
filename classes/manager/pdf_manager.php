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
 * PDF export manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/pdflib.php');

/**
 * PDF export manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_manager {

    /**
     * Export students list to PDF.
     *
     * @param string $risklevel Risk level filter (optional)
     * @return void Sends PDF to browser
     */
    public function export_students_pdf($risklevel = null) {
        global $DB;

        $tracker = new student_tracker();
        $students = $tracker->get_students_at_risk($risklevel, 0);

        // Create PDF.
        $pdf = new \pdf();

        // Set document information.
        $pdf->SetCreator('Student Monitor - UNCHK');
        $pdf->SetAuthor('UNCHK');
        $pdf->SetTitle(get_string('studentreport', 'local_student_monitor'));
        $pdf->SetSubject(get_string('studentmonitorreport', 'local_student_monitor'));

        // Add a page.
        $pdf->AddPage();

        // Set font.
        $pdf->SetFont('helvetica', 'B', 16);

        // Title.
        $pdf->Cell(0, 10, get_string('studentreport', 'local_student_monitor'), 0, 1, 'C');
        $pdf->Ln(5);

        // Date.
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, get_string('generatedon', 'local_student_monitor') . ': ' . userdate(time()), 0, 1, 'R');
        $pdf->Ln(5);

        if ($risklevel) {
            $pdf->Cell(0, 5, get_string('risklevel', 'local_student_monitor') . ': ' . $risklevel, 0, 1, 'L');
            $pdf->Ln(5);
        }

        // Table header.
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);

        $pdf->Cell(60, 7, get_string('studentname', 'local_student_monitor'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, get_string('risklevel', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->Cell(25, 7, get_string('inactivitydays', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->Cell(25, 7, get_string('missingassignments', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->Cell(25, 7, get_string('notificationcount', 'local_student_monitor'), 1, 1, 'C', true);

        // Table content.
        $pdf->SetFont('helvetica', '', 9);

        foreach ($students as $student) {
            $pdf->Cell(60, 6, $student->fullname, 1, 0, 'L');
            $pdf->Cell(30, 6, $student->risk_level, 1, 0, 'C');
            $pdf->Cell(25, 6, $student->inactivity_days, 1, 0, 'C');
            $pdf->Cell(25, 6, $student->missing_activities, 1, 0, 'C');
            $pdf->Cell(25, 6, $student->notification_count, 1, 1, 'C');
        }

        // Summary.
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, get_string('summary', 'local_student_monitor'), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, get_string('totalstudents', 'local_student_monitor') . ': ' . count($students), 0, 1, 'L');

        // Output PDF.
        $filename = 'student_monitor_report_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Export detailed report to PDF.
     *
     * @return void Sends PDF to browser
     */
    public function export_detailed_report_pdf() {
        global $DB;

        $tracker = new student_tracker();
        $stats = $tracker->get_statistics();
        $reportingmanager = new reporting_manager();

        // Create PDF.
        $pdf = new \pdf();

        // Set document information.
        $pdf->SetCreator('Student Monitor - UNCHK');
        $pdf->SetAuthor('UNCHK');
        $pdf->SetTitle(get_string('detailedreport', 'local_student_monitor'));
        $pdf->SetSubject(get_string('studentmonitordetailedreport', 'local_student_monitor'));

        // Add a page.
        $pdf->AddPage();

        // Set font.
        $pdf->SetFont('helvetica', 'B', 18);

        // Title.
        $pdf->Cell(0, 15, get_string('detailedreport', 'local_student_monitor'), 0, 1, 'C');
        $pdf->Ln(5);

        // Date range.
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, get_string('generatedon', 'local_student_monitor') . ': ' . userdate(time()), 0, 1, 'R');
        $pdf->Ln(10);

        // Overview section.
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, get_string('overview', 'local_student_monitor'), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(80, 7, get_string('totalstudents', 'local_student_monitor') . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, $stats->total_students, 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(80, 7, get_string('studentsatrisk', 'local_student_monitor') . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, ($stats->critical + $stats->high), 0, 1, 'L');

        $pdf->Ln(5);

        // Risk distribution section.
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, get_string('riskdistribution', 'local_student_monitor'), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetFillColor(220, 53, 69);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 7, get_string('risk_critical', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, $stats->critical, 1, 1, 'C');

        $pdf->SetFillColor(253, 126, 20);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 7, get_string('risk_high', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, $stats->high, 1, 1, 'C');

        $pdf->SetFillColor(255, 193, 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 7, get_string('risk_medium', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->Cell(0, 7, $stats->medium, 1, 1, 'C');

        $pdf->SetFillColor(40, 167, 69);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 7, get_string('risk_low', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, $stats->low, 1, 1, 'C');

        $pdf->Ln(10);

        // Footer.
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, get_string('generatedby', 'local_student_monitor') . ' Student Monitor - UNCHK', 0, 1, 'C');

        // Output PDF.
        $filename = 'student_monitor_detailed_report_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Export notification history to PDF.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return void Sends PDF to browser
     */
    public function export_notifications_pdf($startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = time() - (30 * 24 * 60 * 60); // Last 30 days.
        }
        if (!$enddate) {
            $enddate = time();
        }

        // Get notifications.
        $notifications = $DB->get_records_sql("
            SELECT n.*, n.type as notification_type, u.firstname, u.lastname,
                   u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
            FROM {local_sm_notifications} n
            JOIN {user} u ON u.id = n.userid
            WHERE n.timecreated >= :startdate AND n.timecreated <= :enddate
            ORDER BY n.timecreated DESC
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        // Create PDF.
        $pdf = new \pdf();
        $pdf->SetCreator('Student Monitor - UNCHK');
        $pdf->SetAuthor('UNCHK');
        $pdf->SetTitle(get_string('notificationhistory', 'local_student_monitor'));

        $pdf->AddPage();

        // Title.
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, get_string('notificationhistory', 'local_student_monitor'), 0, 1, 'C');
        $pdf->Ln(5);

        // Date range.
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, userdate($startdate) . ' - ' . userdate($enddate), 0, 1, 'C');
        $pdf->Ln(5);

        // Table header.
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(50, 7, get_string('student', 'local_student_monitor'), 1, 0, 'L', true);
        $pdf->Cell(40, 7, get_string('notificationtype', 'local_student_monitor'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, get_string('timesent', 'local_student_monitor'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, get_string('status', 'local_student_monitor'), 1, 1, 'C', true);

        // Table content.
        $pdf->SetFont('helvetica', '', 8);
        foreach ($notifications as $notification) {
            $fullname = $notification->firstname . ' ' . $notification->lastname;
            $pdf->Cell(50, 6, $fullname, 1, 0, 'L');
            $pdf->Cell(40, 6, $notification->notification_type, 1, 0, 'L');
            $pdf->Cell(30, 6, userdate($notification->timecreated, get_string('strftimedatetimeshort')), 1, 0, 'C');
            $pdf->Cell(30, 6, $notification->status, 1, 1, 'C');
        }

        // Summary.
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, get_string('total', 'local_student_monitor') . ': ' . count($notifications), 0, 1, 'L');

        // Output PDF.
        $filename = 'notifications_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
    }
}
