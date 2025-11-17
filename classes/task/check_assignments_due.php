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
 * Scheduled task to check upcoming assignment deadlines.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to check for upcoming assignment deadlines and send reminders.
 */
class check_assignments_due extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_check_assignments_due', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping assignment check.');
            return;
        }

        if (!get_config('local_student_monitor', 'assignment_reminders_enabled')) {
            mtrace('Assignment reminders are disabled. Skipping.');
            return;
        }

        mtrace('Starting assignment deadline check...');

        // Get reminder days configuration.
        $reminderdays = get_config('local_student_monitor', 'reminder_days') ?: '7,3,1';
        $reminderdays = array_map('intval', explode(',', $reminderdays));
        mtrace('Reminder days: ' . implode(', ', $reminderdays));

        $notificationmanager = new \local_student_monitor\manager\notification_manager();
        $totalreminders = 0;

        // For each reminder day, find assignments due in that many days.
        foreach ($reminderdays as $days) {
            mtrace("Checking assignments due in {$days} day(s)...");

            // Calculate time window (today + days, with some tolerance).
            $targetdate = strtotime("+{$days} days", strtotime('today'));
            $starttime = $targetdate;
            $endtime = $targetdate + 86400; // +24 hours.

            // Find assignments due in this window.
            $sql = "SELECT a.*
                      FROM {assign} a
                     WHERE a.duedate >= :starttime
                       AND a.duedate < :endtime";

            $assignments = $DB->get_records_sql($sql, [
                'starttime' => $starttime,
                'endtime' => $endtime,
            ]);

            mtrace("  Found " . count($assignments) . " assignments due in {$days} day(s)");

            foreach ($assignments as $assignment) {
                // Get enrolled students in this course.
                $context = \context_course::instance($assignment->course);
                $students = get_enrolled_users($context, 'mod/assign:submit', 0, 'u.id, u.firstname, u.lastname, u.email');

                $remindercount = 0;

                foreach ($students as $student) {
                    // Check if student has already submitted.
                    $submission = $DB->get_record('assign_submission', [
                        'assignment' => $assignment->id,
                        'userid' => $student->id,
                    ]);

                    if ($submission && $submission->status == 'submitted') {
                        continue; // Already submitted, skip.
                    }

                    // Check if reminder already sent for this assignment/user/day combination.
                    $type = 'assignment_reminder';
                    $metadata = json_encode(['assignid' => $assignment->id, 'daysuntildue' => $days]);

                    // Check for recent notification.
                    $sql = "SELECT id
                              FROM {local_sm_notifications}
                             WHERE userid = :userid
                               AND type = :type
                               AND metadata LIKE :metadata
                               AND timecreated > :threshold
                             LIMIT 1";

                    $exists = $DB->record_exists_sql($sql, [
                        'userid' => $student->id,
                        'type' => $type,
                        'metadata' => '%"assignid":' . $assignment->id . '%',
                        'threshold' => time() - 86400, // Within last 24 hours.
                    ]);

                    if (!$exists) {
                        // Create reminder notification.
                        $notificationid = $notificationmanager->create_assignment_reminder(
                            $student->id,
                            $assignment->id,
                            $days
                        );

                        if ($notificationid) {
                            $remindercount++;
                        }
                    }
                }

                if ($remindercount > 0) {
                    mtrace("    Assignment '{$assignment->name}': {$remindercount} reminders created");
                    $totalreminders += $remindercount;
                }
            }
        }

        mtrace('Assignment deadline check complete.');
        mtrace("Total reminders created: {$totalreminders}");
    }
}
