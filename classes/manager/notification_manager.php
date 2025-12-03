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
 * Notification manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class notification_manager
 *
 * Manages all notification-related operations.
 */
class notification_manager {

    /**
     * Create a new notification.
     *
     * @param int $userid User ID of recipient
     * @param string $type Notification type
     * @param string $subject Subject line
     * @param string $message Message body
     * @param int|null $courseid Course ID (optional)
     * @param array $channels Array of channels (email, moodle, sms, whatsapp)
     * @param array $metadata Additional metadata
     * @return int Notification ID
     */
    public function create_notification($userid, $type, $subject, $message, $courseid = null, $channels = ['email', 'moodle'], $metadata = []) {
        global $DB, $USER;

        $notification = new \stdClass();
        $notification->userid = $userid;
        $notification->courseid = $courseid;
        $notification->type = $type;
        $notification->status = 'pending';
        $notification->subject = $subject;
        $notification->message = $message;
        $notification->timecreated = time();
        $notification->sentby = $USER->id;
        $notification->metadata = json_encode($metadata);
        $notification->channels = implode(',', $channels);

        $notificationid = $DB->insert_record('local_sm_notifications', $notification);

        // Log the action.
        $this->log_action('notification_created', $USER->id, $notificationid, [
            'type' => $type,
            'userid' => $userid,
        ]);

        return $notificationid;
    }

    /**
     * Create an inactivity notification.
     *
     * @param int $userid User ID
     * @param string $level Level (level1, level2, level3)
     * @param int $days Number of days inactive
     * @return int Notification ID
     */
    public function create_inactivity_notification($userid, $level, $days) {
        global $DB;

        // Get user.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Get template.
        $type = 'inactivity_' . $level;
        $template = $this->get_template($type);

        if (!$template) {
            debugging('Template not found for type: ' . $type, DEBUG_DEVELOPER);
            return false;
        }

        // Calculate risk level.
        $risklevel = $this->calculate_risk_level_from_days($days);

        // Prepare data for placeholder replacement.
        $data = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => fullname($user),
            'email' => $user->email,
            'days' => $days,
            'lastaccess' => $user->lastaccess ? userdate($user->lastaccess) : get_string('never'),
            'riskLevel' => $risklevel,
            'supportemail' => get_config('local_student_monitor', 'support_email'),
            'supportphone' => get_config('local_student_monitor', 'support_phone'),
            'institutionname' => local_student_monitor_get_institution_name(),
        ];

        // Replace placeholders.
        $subject = $this->replace_placeholders($template->subject, $user, $data);
        $message = $this->replace_placeholders($template->body, $user, $data);

        // Get enabled channels.
        $channels = $this->get_enabled_channels();

        // Create notification.
        return $this->create_notification(
            $userid,
            $type,
            $subject,
            $message,
            null,
            $channels,
            ['days' => $days, 'risk_level' => $risklevel]
        );
    }

    /**
     * Create a new content notification.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $cmid Course module ID
     * @return int Notification ID
     */
    public function create_new_content_notification($userid, $courseid, $cmid) {
        global $DB;

        // Get user.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Get course.
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Get course module.
        $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
        $modinfo = get_fast_modinfo($courseid);
        $cminfo = $modinfo->get_cm($cmid);

        // Get template.
        $template = $this->get_template('new_content');

        if (!$template) {
            return false;
        }

        // Prepare data.
        $data = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'coursename' => $course->fullname,
            'modulename' => $cminfo->name,
            'modulelink' => $cminfo->url->out(),
        ];

        // Replace placeholders.
        $subject = $this->replace_placeholders($template->subject, $user, $data);
        $message = $this->replace_placeholders($template->body, $user, $data);

        // Get enabled channels.
        $channels = $this->get_enabled_channels();

        // Create notification.
        return $this->create_notification(
            $userid,
            'new_content',
            $subject,
            $message,
            $courseid,
            $channels,
            ['cmid' => $cmid, 'modulename' => $cminfo->name]
        );
    }

    /**
     * Create an assignment reminder notification.
     *
     * @param int $userid User ID
     * @param int $assignid Assignment ID
     * @param int $daysuntildue Days until due date
     * @return int Notification ID
     */
    public function create_assignment_reminder($userid, $assignid, $daysuntildue) {
        global $DB;

        // Get user.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Get assignment.
        $assign = $DB->get_record('assign', ['id' => $assignid], '*', MUST_EXIST);

        // Get course.
        $course = $DB->get_record('course', ['id' => $assign->course], '*', MUST_EXIST);

        // Get course module.
        $cm = get_coursemodule_from_instance('assign', $assignid, $assign->course, false, MUST_EXIST);

        // Determine template type based on days.
        $templatetype = 'assignment_reminder_' . $daysuntildue . 'days';
        $template = $this->get_template($templatetype);

        // Fallback to generic reminder template.
        if (!$template) {
            $templatetype = 'assignment_reminder';
            $template = $this->get_template($templatetype);
        }

        if (!$template) {
            return false;
        }

        // Prepare data.
        $data = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'assignmentname' => $assign->name,
            'duedate' => userdate($assign->duedate),
            'coursename' => $course->fullname,
            'submissionlink' => new \moodle_url('/mod/assign/view.php', ['id' => $cm->id]),
        ];

        // Replace placeholders.
        $subject = $this->replace_placeholders($template->subject, $user, $data);
        $message = $this->replace_placeholders($template->body, $user, $data);

        // Get enabled channels.
        $channels = $this->get_enabled_channels();

        // Create notification.
        return $this->create_notification(
            $userid,
            'assignment_reminder',
            $subject,
            $message,
            $assign->course,
            $channels,
            ['assignid' => $assignid, 'daysuntildue' => $daysuntildue]
        );
    }

    /**
     * Replace placeholders in a template.
     *
     * @param string $template Template string
     * @param \stdClass $user User object
     * @param array $data Additional data
     * @return string Processed string
     */
    public function replace_placeholders($template, $user, $data = []) {
        // Default placeholders from user object.
        $placeholders = [
            '{firstname}' => $user->firstname ?? '',
            '{lastname}' => $user->lastname ?? '',
            '{fullname}' => fullname($user),
            '{email}' => $user->email ?? '',
            '{username}' => $user->username ?? '',
        ];

        // Add custom data.
        foreach ($data as $key => $value) {
            $placeholders['{' . $key . '}'] = $value;
        }

        // Add system placeholders.
        $placeholders['{currentdate}'] = userdate(time());
        $placeholders['{institutionname}'] = local_student_monitor_get_institution_name();

        // Replace all placeholders.
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    /**
     * Get a template by type.
     *
     * @param string $type Template type
     * @param string $lang Language code
     * @return \stdClass|false Template object or false
     */
    public function get_template($type, $lang = 'fr') {
        global $DB;

        // Try to get template for specified language.
        $template = $DB->get_record('local_sm_templates', [
            'type' => $type,
            'language' => $lang,
            'is_default' => 1,
        ]);

        // Fallback to French if not found.
        if (!$template && $lang != 'fr') {
            $template = $DB->get_record('local_sm_templates', [
                'type' => $type,
                'language' => 'fr',
                'is_default' => 1,
            ]);
        }

        return $template;
    }

    /**
     * Get enabled notification channels.
     *
     * @return array Array of enabled channels
     */
    protected function get_enabled_channels() {
        $channels = [];

        if (get_config('local_student_monitor', 'channel_email')) {
            $channels[] = 'email';
        }

        if (get_config('local_student_monitor', 'channel_moodle')) {
            $channels[] = 'moodle';
        }

        if (get_config('local_student_monitor', 'channel_sms')) {
            $channels[] = 'sms';
        }

        if (get_config('local_student_monitor', 'channel_whatsapp')) {
            $channels[] = 'whatsapp';
        }

        // Always include at least email if nothing is enabled.
        if (empty($channels)) {
            $channels[] = 'email';
        }

        return $channels;
    }

    /**
     * Calculate risk level from days of inactivity.
     *
     * @param int $days Days of inactivity
     * @return string Risk level
     */
    protected function calculate_risk_level_from_days($days) {
        $thresholds = \local_student_monitor\risk_level::get_inactivity_thresholds();

        if ($days >= $thresholds['level3']) {
            return \local_student_monitor\risk_level::CRITICAL;
        } else if ($days >= $thresholds['level2']) {
            return \local_student_monitor\risk_level::HIGH;
        } else if ($days >= $thresholds['level1']) {
            return \local_student_monitor\risk_level::MEDIUM;
        }
        return \local_student_monitor\risk_level::LOW;
    }

    /**
     * Log an action.
     *
     * @param string $action Action name
     * @param int $userid User ID
     * @param int $targetid Target ID
     * @param array $details Additional details
     */
    protected function log_action($action, $userid, $targetid, $details = []) {
        global $DB;

        $log = new \stdClass();
        $log->action = $action;
        $log->userid = $userid;
        $log->targetid = $targetid;
        $log->details = json_encode($details);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Update notification status.
     *
     * @param int $notificationid Notification ID
     * @param string $status New status
     * @return bool Success
     */
    public function update_notification_status($notificationid, $status) {
        global $DB;

        $update = new \stdClass();
        $update->id = $notificationid;
        $update->status = $status;

        if ($status == 'sent') {
            $update->timesent = time();
        } else if ($status == 'read') {
            $update->timeread = time();
        }

        return $DB->update_record('local_sm_notifications', $update);
    }

    /**
     * Check if a similar notification was recently sent.
     *
     * @param int $userid User ID
     * @param string $type Notification type
     * @param int $threshold Time threshold in seconds
     * @return bool True if recent notification exists
     */
    public function has_recent_notification($userid, $type, $threshold = 86400) {
        global $DB;

        $sql = "SELECT id
                  FROM {local_sm_notifications}
                 WHERE userid = :userid
                   AND type = :type
                   AND timecreated > :threshold
                 LIMIT 1";

        $params = [
            'userid' => $userid,
            'type' => $type,
            'threshold' => time() - $threshold,
        ];

        return $DB->record_exists_sql($sql, $params);
    }
}
