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
 * Event observer for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer class.
 */
class observer {

    /**
     * Observer for course module created event.
     *
     * Creates notifications for students when new content is added.
     *
     * @param \core\event\course_module_created $event
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            return;
        }

        $courseid = $event->courseid;
        $cmid = $event->objectid;

        // Check if new content notifications are enabled for this course.
        $config = $DB->get_record('local_sm_config', [
            'courseid' => $courseid,
            'config_type' => 'new_content',
            'config_key' => 'enabled',
        ]);

        // If no config, default to enabled.
        if ($config && !$config->enabled) {
            return;
        }

        // Get enrolled students.
        $context = \context_course::instance($courseid);
        $students = get_enrolled_users($context, 'mod/assign:submit', 0, 'u.id');

        $notificationmanager = new \local_student_monitor\manager\notification_manager();

        foreach ($students as $student) {
            $notificationmanager->create_new_content_notification($student->id, $courseid, $cmid);
        }
    }

    /**
     * Observer for course module updated event.
     *
     * @param \core\event\course_module_updated $event
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        // Optionally handle module updates.
        // For now, we only notify on new content, not updates.
    }

    /**
     * Observer for forum discussion created event.
     *
     * Checks if this is from the institutional forum and sends announcements.
     *
     * @param \mod_forum\event\discussion_created $event
     */
    public static function forum_discussion_created(\mod_forum\event\discussion_created $event) {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            return;
        }

        $forumid = $event->other['forumid'];
        $institutionalforumid = get_config('local_student_monitor', 'institutional_forum_id');

        // Check if this is the institutional forum.
        if (empty($institutionalforumid) || $forumid != $institutionalforumid) {
            return;
        }

        // Get the discussion details.
        $discussionid = $event->objectid;
        $discussion = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);
        $post = $DB->get_record('forum_posts', ['discussion' => $discussionid], '*', MUST_EXIST);

        // Get all students in the system.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        if (!$studentrole) {
            return;
        }

        $sql = "SELECT DISTINCT u.id
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                 WHERE ra.roleid = :roleid
                   AND u.suspended = 0
                   AND u.deleted = 0";

        $students = $DB->get_records_sql($sql, ['roleid' => $studentrole->id]);

        // Get template.
        $notificationmanager = new \local_student_monitor\manager\notification_manager();
        $template = $notificationmanager->get_template('institutional_announcement');

        if (!$template) {
            return;
        }

        // Create notifications for all students.
        foreach ($students as $student) {
            $user = $DB->get_record('user', ['id' => $student->id]);

            $data = [
                'title' => $discussion->name,
                'message' => strip_tags($post->message),
            ];

            $subject = $notificationmanager->replace_placeholders($template->subject, $user, $data);
            $message = $notificationmanager->replace_placeholders($template->body, $user, $data);

            $notificationmanager->create_notification(
                $user->id,
                'institutional_announcement',
                $subject,
                $message,
                null,
                ['email', 'moodle'],
                ['discussion_id' => $discussionid]
            );
        }
    }

    /**
     * Observer for assignment submitted event.
     *
     * Updates student tracking when assignment is submitted.
     *
     * @param \mod_assign\event\assessable_submitted $event
     */
    public static function assignment_submitted(\mod_assign\event\assessable_submitted $event) {
        if (!get_config('local_student_monitor', 'enabled')) {
            return;
        }

        $userid = $event->userid;
        $courseid = $event->courseid;

        $tracker = new \local_student_monitor\manager\student_tracker();
        $tracker->update_student_tracking($userid, $courseid);
    }

    /**
     * Observer for user logged in event.
     *
     * Updates last activity tracking.
     *
     * @param \core\event\user_loggedin $event
     */
    public static function user_logged_in(\core\event\user_loggedin $event) {
        if (!get_config('local_student_monitor', 'enabled')) {
            return;
        }

        $userid = $event->userid;

        $tracker = new \local_student_monitor\manager\student_tracker();
        $tracker->update_student_tracking($userid);
    }

    /**
     * Observer for course viewed event.
     *
     * Updates student activity tracking.
     *
     * @param \core\event\course_viewed $event
     */
    public static function course_viewed(\core\event\course_viewed $event) {
        if (!get_config('local_student_monitor', 'enabled')) {
            return;
        }

        $userid = $event->userid;
        $courseid = $event->courseid;

        // Update tracking for this specific course.
        $tracker = new \local_student_monitor\manager\student_tracker();
        $tracker->update_student_tracking($userid, $courseid);
    }
}
