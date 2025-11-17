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
        foreach ($notifstats as $stat) {
            mtrace('  ' . $stat->type . ': ' . $stat->count);
        }

        // TODO: Send this report to administrators via email.

        mtrace('Weekly report generation complete.');
    }
}
