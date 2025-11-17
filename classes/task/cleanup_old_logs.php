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
 * Scheduled task to cleanup old logs.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to cleanup old logs and notifications.
 */
class cleanup_old_logs extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_cleanup_old_logs', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping cleanup.');
            return;
        }

        mtrace('Starting cleanup of old logs and notifications...');

        // Delete logs older than 90 days.
        $logthreshold = time() - (90 * 86400);

        $sql = "DELETE FROM {local_sm_logs}
                 WHERE timecreated < :threshold";

        $deletedlogs = $DB->execute($sql, ['threshold' => $logthreshold]);

        mtrace('Deleted old logs (older than 90 days)');

        // Delete read notifications older than 180 days.
        $notifthreshold = time() - (180 * 86400);

        $sql = "DELETE FROM {local_sm_notifications}
                 WHERE timeread IS NOT NULL
                   AND timeread < :threshold";

        $deletednotifs = $DB->execute($sql, ['threshold' => $notifthreshold]);

        mtrace('Deleted old read notifications (older than 180 days)');

        // Delete failed notifications older than 30 days.
        $failedthreshold = time() - (30 * 86400);

        $sql = "DELETE FROM {local_sm_notifications}
                 WHERE status = 'failed'
                   AND timecreated < :threshold";

        $deletedfailed = $DB->execute($sql, ['threshold' => $failedthreshold]);

        mtrace('Deleted old failed notifications (older than 30 days)');

        mtrace('Cleanup complete.');
    }
}
