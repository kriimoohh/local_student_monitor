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
 * Scheduled task to update student tracking data.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to update student tracking data daily.
 */
class update_student_tracking extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_update_student_tracking', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping tracking update.');
            return;
        }

        mtrace('Starting student tracking update...');

        $tracker = new \local_student_monitor\manager\student_tracker();

        // Get all students.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        if (!$studentrole) {
            mtrace('Warning: Student role not found!');
            return;
        }

        $sql = "SELECT DISTINCT u.id
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                 WHERE ra.roleid = :roleid
                   AND u.suspended = 0
                   AND u.deleted = 0";

        $students = $DB->get_records_sql($sql, ['roleid' => $studentrole->id]);

        mtrace('Updating tracking for ' . count($students) . ' students...');

        $updatecount = 0;

        foreach ($students as $student) {
            $tracker->update_student_tracking($student->id);
            $updatecount++;

            if ($updatecount % 100 == 0) {
                mtrace("  Updated {$updatecount} students...");
            }
        }

        mtrace('Student tracking update complete.');
        mtrace("  Total students updated: {$updatecount}");
    }
}
