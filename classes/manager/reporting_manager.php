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
 * Reporting manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class reporting_manager
 *
 * Manages reports and data export.
 */
class reporting_manager {

    /**
     * Export students data to CSV.
     *
     * @param string $risklevel Optional filter by risk level
     * @return string CSV content
     */
    public function export_students_csv($risklevel = null) {
        global $DB;

        $sql = "SELECT st.*, u.firstname, u.lastname, u.email, u.lastaccess,
                       u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                  FROM {local_sm_student_tracking} st
                  JOIN {user} u ON u.id = st.userid
                 WHERE 1=1";

        $params = [];

        if ($risklevel) {
            $sql .= " AND st.risk_level = :risklevel";
            $params['risklevel'] = $risklevel;
        }

        $sql .= " ORDER BY
                    CASE st.risk_level
                        WHEN 'CRITICAL' THEN 1
                        WHEN 'HIGH' THEN 2
                        WHEN 'MEDIUM' THEN 3
                        ELSE 4
                    END,
                    st.inactivity_days DESC";

        $students = $DB->get_records_sql($sql, $params);

        // Build CSV.
        $csv = [];

        // Header.
        $csv[] = [
            'ID',
            'Prénom',
            'Nom',
            'Email',
            'Niveau de risque',
            'Jours d\'inactivité',
            'Devoirs manquants',
            'Notifications envoyées',
            'Intervention nécessaire',
            'Dernière activité',
            'Dernière mise à jour',
        ];

        // Data rows.
        foreach ($students as $student) {
            $csv[] = [
                $student->userid,
                $student->firstname,
                $student->lastname,
                $student->email,
                $student->risk_level,
                $student->inactivity_days,
                $student->missing_activities,
                $student->notification_count,
                $student->intervention_needed ? 'Oui' : 'Non',
                $student->last_activity ? userdate($student->last_activity) : 'Jamais',
                userdate($student->timeupdated),
            ];
        }

        return $this->array_to_csv($csv);
    }

    /**
     * Export notifications data to CSV.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return string CSV content
     */
    public function export_notifications_csv($startdate = null, $enddate = null) {
        global $DB;

        $sql = "SELECT n.*, u.firstname, u.lastname, u.email,
                       u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                  FROM {local_sm_notifications} n
                  JOIN {user} u ON u.id = n.userid
                 WHERE 1=1";

        $params = [];

        if ($startdate) {
            $sql .= " AND n.timecreated >= :startdate";
            $params['startdate'] = $startdate;
        }

        if ($enddate) {
            $sql .= " AND n.timecreated <= :enddate";
            $params['enddate'] = $enddate;
        }

        $sql .= " ORDER BY n.timecreated DESC";

        $notifications = $DB->get_records_sql($sql, $params);

        // Build CSV.
        $csv = [];

        // Header.
        $csv[] = [
            'ID',
            'Utilisateur',
            'Email',
            'Type',
            'Statut',
            'Sujet',
            'Canaux',
            'Date création',
            'Date envoi',
            'Date lecture',
        ];

        // Data rows.
        foreach ($notifications as $notif) {
            $csv[] = [
                $notif->id,
                $notif->firstname . ' ' . $notif->lastname,
                $notif->email,
                $notif->type,
                $notif->status,
                $notif->subject,
                $notif->channels,
                userdate($notif->timecreated),
                $notif->timesent ? userdate($notif->timesent) : '-',
                $notif->timeread ? userdate($notif->timeread) : '-',
            ];
        }

        return $this->array_to_csv($csv);
    }

    /**
     * Generate weekly report data.
     *
     * @return \stdClass Report data
     */
    public function generate_weekly_report() {
        global $DB;

        $report = new \stdClass();
        $weekago = time() - (7 * 86400);

        // Student statistics.
        $tracker = new student_tracker();
        $report->student_stats = $tracker->get_statistics();

        // Notification statistics.
        $sql = "SELECT type, COUNT(*) as count,
                       SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                       SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount,
                       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                  FROM {local_sm_notifications}
                 WHERE timecreated >= :weekago
              GROUP BY type";

        $report->notification_stats = $DB->get_records_sql($sql, ['weekago' => $weekago]);

        // Top at-risk students.
        $report->top_at_risk = $tracker->get_students_at_risk('CRITICAL', 10);

        // Recent interventions.
        $sql = "SELECT COUNT(*) as count
                  FROM {local_sm_student_tracking}
                 WHERE intervention_needed = 1
                   AND timeupdated >= :weekago";

        $result = $DB->get_record_sql($sql, ['weekago' => $weekago]);
        $report->recent_interventions = $result->count;

        // Read rate.
        $sql = "SELECT COUNT(*) as total,
                       SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount
                  FROM {local_sm_notifications}
                 WHERE timesent >= :weekago";

        $result = $DB->get_record_sql($sql, ['weekago' => $weekago]);
        $report->read_rate = 0;
        if ($result->total > 0) {
            $report->read_rate = round(($result->readcount / $result->total) * 100, 1);
        }

        $report->generated_at = time();

        return $report;
    }

    /**
     * Convert array to CSV string.
     *
     * @param array $data Array of rows
     * @return string CSV content
     */
    protected function array_to_csv($data) {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get notification trends data for charts.
     *
     * @param int $days Number of days to analyze
     * @return array Array of data points
     */
    public function get_notification_trends($days = 30) {
        global $DB;

        $startdate = time() - ($days * 86400);

        $sql = "SELECT DATE(FROM_UNIXTIME(timecreated)) as date,
                       type,
                       COUNT(*) as count
                  FROM {local_sm_notifications}
                 WHERE timecreated >= :startdate
              GROUP BY DATE(FROM_UNIXTIME(timecreated)), type
              ORDER BY date ASC";

        return $DB->get_records_sql($sql, ['startdate' => $startdate]);
    }

    /**
     * Get risk level distribution data for charts.
     *
     * @return array Array of data points
     */
    public function get_risk_distribution() {
        global $DB;

        $sql = "SELECT risk_level, COUNT(*) as count
                  FROM {local_sm_student_tracking}
              GROUP BY risk_level";

        return $DB->get_records_sql($sql);
    }
}
