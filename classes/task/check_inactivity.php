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
 * Scheduled task to check student inactivity.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to check for student inactivity and create notifications.
 */
class check_inactivity extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_check_inactivity', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping inactivity check.');
            return;
        }

        mtrace('Starting student inactivity check...');

        // Get inactivity thresholds from settings.
        $threshold1 = (int) get_config('local_student_monitor', 'inactivity_threshold_1') ?: 3;
        $threshold2 = (int) get_config('local_student_monitor', 'inactivity_threshold_2') ?: 7;
        $threshold3 = (int) get_config('local_student_monitor', 'inactivity_threshold_3') ?: 14;

        mtrace("Thresholds: Level 1={$threshold1} days, Level 2={$threshold2} days, Level 3={$threshold3} days");

        // Get all students (users with student role).
        $students = $this->get_active_students();
        mtrace('Found ' . count($students) . ' active students to check.');

        $notificationmanager = new \local_student_monitor\manager\notification_manager();
        $trackerManager = new \local_student_monitor\manager\student_tracker();

        $level1count = 0;
        $level2count = 0;
        $level3count = 0;

        foreach ($students as $student) {
            // Calculate days of inactivity.
            $lastaccess = $student->lastaccess ?: 0;
            if ($lastaccess == 0) {
                continue; // Never logged in, skip for now.
            }

            $daysInactive = floor((time() - $lastaccess) / 86400);

            // Determine which level to trigger.
            $level = null;
            if ($daysInactive >= $threshold3) {
                $level = 'level3';
            } else if ($daysInactive >= $threshold2) {
                $level = 'level2';
            } else if ($daysInactive >= $threshold1) {
                $level = 'level1';
            }

            if ($level) {
                // Check if we already sent this level notification recently.
                $type = 'inactivity_' . $level;
                $threshold = 86400 * ($daysInactive >= $threshold3 ? 7 : 3); // Don't resend for 3-7 days.

                if (!$notificationmanager->has_recent_notification($student->id, $type, $threshold)) {
                    // Create notification.
                    $notificationid = $notificationmanager->create_inactivity_notification(
                        $student->id,
                        $level,
                        $daysInactive
                    );

                    if ($notificationid) {
                        mtrace("  Created {$level} notification for user {$student->id} ({$daysInactive} days inactive)");

                        switch ($level) {
                            case 'level1':
                                $level1count++;
                                break;
                            case 'level2':
                                $level2count++;
                                break;
                            case 'level3':
                                $level3count++;
                                break;
                        }
                    }
                }

                // Update student tracking.
                $trackerManager->update_student_tracking($student->id);
            }
        }

        mtrace('Inactivity check complete.');
        mtrace("  Level 1 notifications created: {$level1count}");
        mtrace("  Level 2 notifications created: {$level2count}");
        mtrace("  Level 3 notifications created: {$level3count}");
    }

    /**
     * Get all active students in the system.
     *
     * @return array Array of user records
     */
    protected function get_active_students() {
        global $DB;

        // Get the student role ID.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        if (!$studentrole) {
            mtrace('Warning: Student role not found!');
            return [];
        }

        // Get all users with student role who are not suspended or deleted.
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.lastaccess
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                 WHERE ra.roleid = :roleid
                   AND u.suspended = 0
                   AND u.deleted = 0
                 ORDER BY u.lastaccess ASC";

        return $DB->get_records_sql($sql, ['roleid' => $studentrole->id]);
    }
}
