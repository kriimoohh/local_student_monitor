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
 * Scheduled task to generate weekly reports.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to generate weekly summary reports.
 */
class generate_weekly_report extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_generate_weekly_report', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping weekly report.');
            return;
        }

        mtrace('Generating weekly report...');

        $tracker = new \local_student_monitor\manager\student_tracker();

        // Get statistics.
        $stats = $tracker->get_statistics();

        mtrace('Weekly Student Monitor Report');
        mtrace('=============================');
        mtrace('Total students tracked: ' . $stats->total_students);
        mtrace('');
        mtrace('Risk Level Distribution:');
        mtrace('  CRITIQUE: ' . $stats->critique);
        mtrace('  ÉLEVÉ: ' . $stats->eleve);
        mtrace('  MOYEN: ' . $stats->moyen);
        mtrace('  FAIBLE: ' . $stats->faible);
        mtrace('');
        mtrace('Students needing intervention: ' . $stats->intervention_needed);
        mtrace('Average inactivity days: ' . $stats->avg_inactivity);
        mtrace('');

        // Get notification stats for the week.
        $weekago = time() - (7 * 86400);

        $sql = "SELECT type, COUNT(*) as count
                  FROM {local_sm_notifications}
                 WHERE timecreated >= :weekago
                 GROUP BY type";

        $notifstats = $DB->get_records_sql($sql, ['weekago' => $weekago]);

        mtrace('Notifications sent this week:');
        $totalnotifications = 0;
        $automaticalerts = 0;
        $manualalerts = 0;

        foreach ($notifstats as $stat) {
            mtrace('  ' . $stat->type . ': ' . $stat->count);
            $totalnotifications += $stat->count;

            // Count automatic alerts (inactivity levels and risk-based alerts).
            if (strpos($stat->type, 'inactivity_level') !== false ||
                strpos($stat->type, 'risk_') !== false ||
                $stat->type === 'assignment_reminder') {
                $automaticalerts += $stat->count;
            } else if ($stat->type === 'manual_alert') {
                $manualalerts += $stat->count;
            }
        }

        mtrace('');
        mtrace('Summary:');
        mtrace('  Total notifications: ' . $totalnotifications);
        mtrace('  Automatic alerts: ' . $automaticalerts);
        mtrace('  Manual alerts: ' . $manualalerts);

        // Get automatic alerts details.
        if ($automaticalerts > 0) {
            mtrace('');
            mtrace('Automatic Alerts Breakdown:');

            $autosql = "SELECT type, COUNT(*) as count,
                               SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                               SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount,
                               SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                          FROM {local_sm_notifications}
                         WHERE timecreated >= :weekago
                           AND (type LIKE 'inactivity_level%'
                            OR type LIKE 'risk_%'
                            OR type = 'assignment_reminder')
                         GROUP BY type";

            $autostats = $DB->get_records_sql($autosql, ['weekago' => $weekago]);

            foreach ($autostats as $autostat) {
                $readrate = $autostat->sent > 0 ? round(($autostat->readcount / $autostat->sent) * 100, 1) : 0;
                mtrace('  ' . $autostat->type . ':');
                mtrace('    Total: ' . $autostat->count . ' | Sent: ' . $autostat->sent .
                       ' | Read: ' . $autostat->readcount . ' (' . $readrate . '%) | Failed: ' . $autostat->failed);
            }
        }

        // TODO: Send this report to administrators via email.

        mtrace('');
        mtrace('Weekly report generation complete.');
    }
}
