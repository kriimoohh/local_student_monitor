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
 * Parent/Guardian notification manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Parent/Guardian manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class parent_guardian_manager {

    /**
     * Register a parent/guardian for a student.
     *
     * @param int $studentid Student user ID
     * @param string $parentname Parent/guardian name
     * @param string $parentemail Parent/guardian email
     * @param string $parentphone Parent/guardian phone
     * @param string $relationship Relationship to student
     * @param bool $notifyenabled Enable notifications
     * @return int Parent record ID
     */
    public function register_parent($studentid, $parentname, $parentemail, $parentphone = '',
                                   $relationship = 'parent', $notifyenabled = true) {
        global $DB;

        $parent = new \stdClass();
        $parent->student_id = $studentid;
        $parent->parent_name = $parentname;
        $parent->parent_email = $parentemail;
        $parent->parent_phone = $parentphone;
        $parent->relationship = $relationship;
        $parent->notify_enabled = $notifyenabled ? 1 : 0;
        $parent->notify_frequency = 'critical'; // critical, weekly, monthly.
        $parent->language = 'fr';
        $parent->timecreated = time();
        $parent->timemodified = time();

        // Check if table exists.
        if ($DB->get_manager()->table_exists('local_sm_parents')) {
            return $DB->insert_record('local_sm_parents', $parent);
        } else {
            // Fallback to config table.
            $config = new \stdClass();
            $config->courseid = 0;
            $config->config_type = 'parent_guardian';
            $config->config_key = 'student_' . $studentid . '_parent_' . time();
            $config->config_value = json_encode($parent);
            $config->timecreated = time();

            return $DB->insert_record('local_sm_config', $config);
        }
    }

    /**
     * Get parents/guardians for a student.
     *
     * @param int $studentid Student user ID
     * @return array Parent records
     */
    public function get_student_parents($studentid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_parents')) {
            return $DB->get_records('local_sm_parents', ['student_id' => $studentid]);
        } else {
            // Fallback to config table.
            $configs = $DB->get_records_sql("
                SELECT *
                FROM {local_sm_config}
                WHERE config_type = 'parent_guardian'
                  AND config_key LIKE :pattern
            ", ['pattern' => 'student_' . $studentid . '_parent_%']);

            $parents = [];
            foreach ($configs as $config) {
                $parent = json_decode($config->config_value);
                if ($parent && $parent->student_id == $studentid) {
                    $parent->id = $config->id;
                    $parents[] = $parent;
                }
            }
            return $parents;
        }
    }

    /**
     * Notify parents about student's critical risk status.
     *
     * @param int $studentid Student user ID
     * @param string $risklevel Risk level
     * @param object $tracking Student tracking data
     * @return array Notification results
     */
    public function notify_parents_critical($studentid, $risklevel, $tracking) {
        global $DB;

        $parents = $this->get_student_parents($studentid);
        $results = [];

        if (empty($parents)) {
            return $results;
        }

        $student = $DB->get_record('user', ['id' => $studentid]);

        foreach ($parents as $parent) {
            // Check if notifications are enabled.
            if (!$parent->notify_enabled) {
                continue;
            }

            // Check notification frequency.
            if ($parent->notify_frequency === 'critical' && !in_array($risklevel, ['CRITIQUE', 'ÉLEVÉ'])) {
                continue;
            }

            // Check if already notified recently.
            if ($this->was_recently_notified($parent->id, 7)) {
                continue;
            }

            // Prepare notification.
            $subject = get_string('parentnotificationsubject', 'local_student_monitor');
            $message = $this->prepare_parent_message($student, $parent, $risklevel, $tracking);

            // Send via email.
            $success = $this->send_parent_email($parent, $subject, $message);

            // Send via SMS if phone available and enabled.
            if (!empty($parent->parent_phone) && get_config('local_student_monitor', 'parent_sms_enabled')) {
                $smsmessage = $this->prepare_parent_sms($student, $risklevel);
                $this->send_parent_sms($parent->parent_phone, $smsmessage);
            }

            // Log notification.
            $this->log_parent_notification($parent->id, $studentid, $risklevel, $success);

            $results[] = [
                'parent_id' => $parent->id,
                'parent_name' => $parent->parent_name,
                'success' => $success
            ];
        }

        return $results;
    }

    /**
     * Prepare parent notification message.
     *
     * @param object $student Student user
     * @param object $parent Parent record
     * @param string $risklevel Risk level
     * @param object $tracking Tracking data
     * @return string Message
     */
    protected function prepare_parent_message($student, $parent, $risklevel, $tracking) {
        $data = [
            'parentname' => $parent->parent_name,
            'studentname' => fullname($student),
            'risklevel' => get_string('risk_' . strtolower(str_replace('É', 'e', $risklevel)), 'local_student_monitor'),
            'inactivitydays' => $tracking->inactivity_days,
            'missingassignments' => $tracking->missing_assignments,
            'supportemail' => get_config('local_student_monitor', 'supportemail'),
            'supportphone' => get_config('local_student_monitor', 'supportphone')
        ];

        $template = get_string('parentnotificationtemplate', 'local_student_monitor', $data);

        // Add personalized recommendations.
        $recommendations = $this->get_parent_recommendations($risklevel, $tracking);
        $template .= "\n\n" . get_string('recommendations', 'local_student_monitor') . ":\n";
        foreach ($recommendations as $recommendation) {
            $template .= "• " . $recommendation . "\n";
        }

        return $template;
    }

    /**
     * Get recommendations for parents.
     *
     * @param string $risklevel Risk level
     * @param object $tracking Tracking data
     * @return array Recommendations
     */
    protected function get_parent_recommendations($risklevel, $tracking) {
        $recommendations = [];

        if ($tracking->inactivity_days > 7) {
            $recommendations[] = get_string('recommendcontactstudent', 'local_student_monitor');
        }

        if ($tracking->missing_assignments > 3) {
            $recommendations[] = get_string('recommendassignmenthelp', 'local_student_monitor');
        }

        if ($risklevel === 'CRITIQUE') {
            $recommendations[] = get_string('recommendurgencontact', 'local_student_monitor');
            $recommendations[] = get_string('recommendcontactsupervisor', 'local_student_monitor');
        }

        $recommendations[] = get_string('recommendencouragement', 'local_student_monitor');

        return $recommendations;
    }

    /**
     * Prepare SMS message for parent.
     *
     * @param object $student Student
     * @param string $risklevel Risk level
     * @return string SMS message
     */
    protected function prepare_parent_sms($student, $risklevel) {
        return get_string('parentsmstemplate', 'local_student_monitor', [
            'studentname' => fullname($student),
            'risklevel' => $risklevel
        ]);
    }

    /**
     * Send email to parent.
     *
     * @param object $parent Parent record
     * @param string $subject Subject
     * @param string $message Message
     * @return bool Success
     */
    protected function send_parent_email($parent, $subject, $message) {
        // Create parent user object for email.
        $parentuser = new \stdClass();
        $parentuser->id = -1;
        $parentuser->email = $parent->parent_email;
        $parentuser->firstname = $parent->parent_name;
        $parentuser->lastname = '';
        $parentuser->maildisplay = 1;
        $parentuser->mailformat = 1;
        $parentuser->deleted = 0;

        $from = \core_user::get_noreply_user();

        return email_to_user($parentuser, $from, $subject, $message, $message);
    }

    /**
     * Send SMS to parent.
     *
     * @param string $phone Phone number
     * @param string $message Message
     * @return bool Success
     */
    protected function send_parent_sms($phone, $message) {
        $channelmanager = new channel_manager();
        return $channelmanager->send_sms($phone, $message);
    }

    /**
     * Check if parent was recently notified.
     *
     * @param int $parentid Parent ID
     * @param int $days Days threshold
     * @return bool True if recently notified
     */
    protected function was_recently_notified($parentid, $days = 7) {
        global $DB;

        $since = time() - ($days * 24 * 60 * 60);

        $count = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {local_sm_logs}
            WHERE action = 'parent_notified'
              AND details LIKE :parentid
              AND timecreated >= :since
        ", [
            'parentid' => '%"parent_id":' . $parentid . '%',
            'since' => $since
        ]);

        return $count > 0;
    }

    /**
     * Log parent notification.
     *
     * @param int $parentid Parent ID
     * @param int $studentid Student ID
     * @param string $risklevel Risk level
     * @param bool $success Success status
     */
    protected function log_parent_notification($parentid, $studentid, $risklevel, $success) {
        global $DB;

        $log = new \stdClass();
        $log->userid = $studentid;
        $log->action = 'parent_notified';
        $log->details = json_encode([
            'parent_id' => $parentid,
            'risk_level' => $risklevel,
            'success' => $success
        ]);
        $log->timecreated = time();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Send weekly digest to parents.
     *
     * @param int $studentid Student ID
     * @return array Results
     */
    public function send_weekly_digest($studentid) {
        global $DB;

        $parents = $this->get_student_parents($studentid);
        $results = [];

        if (empty($parents)) {
            return $results;
        }

        $student = $DB->get_record('user', ['id' => $studentid]);
        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $studentid]);

        if (!$tracking) {
            return $results;
        }

        foreach ($parents as $parent) {
            // Check if weekly digest is enabled.
            if ($parent->notify_frequency !== 'weekly') {
                continue;
            }

            // Prepare digest.
            $subject = get_string('weeklydigestsubject', 'local_student_monitor');
            $message = $this->prepare_weekly_digest($student, $parent, $tracking);

            // Send email.
            $success = $this->send_parent_email($parent, $subject, $message);

            $results[] = [
                'parent_id' => $parent->id,
                'success' => $success
            ];
        }

        return $results;
    }

    /**
     * Prepare weekly digest for parent.
     *
     * @param object $student Student
     * @param object $parent Parent
     * @param object $tracking Tracking data
     * @return string Message
     */
    protected function prepare_weekly_digest($student, $parent, $tracking) {
        global $DB;

        $message = get_string('weeklydigestintro', 'local_student_monitor', [
            'parentname' => $parent->parent_name,
            'studentname' => fullname($student)
        ]);

        $message .= "\n\n" . get_string('weeklyactivitysummary', 'local_student_monitor') . ":\n\n";

        // Risk level.
        $message .= "• " . get_string('risklevel', 'local_student_monitor') . ": " . $tracking->risk_level . "\n";

        // Last login.
        $lastlogin = $DB->get_field('user', 'lastaccess', ['id' => $student->id]);
        if ($lastlogin) {
            $daysago = floor((time() - $lastlogin) / (24 * 60 * 60));
            $message .= "• " . get_string('lastlogin', 'local_student_monitor') . ": " .
                        userdate($lastlogin) . " (" . $daysago . " " . get_string('daysago', 'local_student_monitor') . ")\n";
        }

        // Missing assignments.
        $message .= "• " . get_string('missingassignments', 'local_student_monitor') . ": " .
                    $tracking->missing_assignments . "\n";

        // Notifications sent.
        $message .= "• " . get_string('notificationssent', 'local_student_monitor') . ": " .
                    $tracking->notification_count . "\n";

        return $message;
    }

    /**
     * Update parent preferences.
     *
     * @param int $parentid Parent ID
     * @param array $preferences Preferences
     * @return bool Success
     */
    public function update_parent_preferences($parentid, $preferences) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_parents')) {
            $parent = $DB->get_record('local_sm_parents', ['id' => $parentid]);
            if (!$parent) {
                return false;
            }

            foreach ($preferences as $key => $value) {
                if (property_exists($parent, $key)) {
                    $parent->$key = $value;
                }
            }

            $parent->timemodified = time();
            return $DB->update_record('local_sm_parents', $parent);
        }

        return false;
    }

    /**
     * Get notification statistics for parents.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return object Statistics
     */
    public function get_parent_notification_stats($startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = strtotime('first day of this month');
        }
        if (!$enddate) {
            $enddate = time();
        }

        $stats = new \stdClass();

        // Count notifications sent.
        $stats->total_notifications = $DB->count_records_sql("
            SELECT COUNT(*)
            FROM {local_sm_logs}
            WHERE action = 'parent_notified'
              AND timecreated >= :startdate
              AND timecreated <= :enddate
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        // Count unique parents notified.
        $logs = $DB->get_records_sql("
            SELECT DISTINCT details
            FROM {local_sm_logs}
            WHERE action = 'parent_notified'
              AND timecreated >= :startdate
              AND timecreated <= :enddate
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        $uniqueparents = [];
        foreach ($logs as $log) {
            $details = json_decode($log->details);
            if ($details && isset($details->parent_id)) {
                $uniqueparents[$details->parent_id] = true;
            }
        }

        $stats->unique_parents = count($uniqueparents);

        // Get total registered parents.
        if ($DB->get_manager()->table_exists('local_sm_parents')) {
            $stats->total_registered = $DB->count_records('local_sm_parents');
        } else {
            $stats->total_registered = 0;
        }

        return $stats;
    }
}
