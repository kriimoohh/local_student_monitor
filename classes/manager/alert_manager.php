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
 * Alert manager class for manual alerts.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class alert_manager
 *
 * Manages manual alerts created by supervisors.
 */
class alert_manager {

    /**
     * Create a manual alert.
     *
     * @param \stdClass $data Alert data from form
     * @return int Alert ID (notification ID)
     */
    public function create_manual_alert($data) {
        global $DB, $USER;

        $notificationmanager = new notification_manager();

        // Get recipients.
        $recipients = $this->get_recipients($data);

        if (empty($recipients)) {
            return false;
        }

        // Prepare message.
        $subject = $data->title;
        $message = $data->description['text'];

        // Add event details to message.
        if (isset($data->eventdate)) {
            $message .= "\n\n";
            $message .= get_string('eventdate', 'local_student_monitor') . ': ' . userdate($data->eventdate);
        }

        if (isset($data->location)) {
            $message .= "\n" . get_string('location') . ': ' . $data->location;
        }

        // Get selected channels.
        $channels = [];
        if (!empty($data->channel_email)) {
            $channels[] = 'email';
        }
        if (!empty($data->channel_moodle)) {
            $channels[] = 'moodle';
        }
        if (!empty($data->channel_sms)) {
            $channels[] = 'sms';
        }
        if (!empty($data->channel_whatsapp)) {
            $channels[] = 'whatsapp';
        }

        // Default to email if none selected.
        if (empty($channels)) {
            $channels = ['email'];
        }

        // Create notifications for each recipient.
        $notificationids = [];
        foreach ($recipients as $recipient) {
            $metadata = [
                'alerttype' => $data->alerttype,
                'eventdate' => $data->eventdate ?? null,
                'manual' => true,
            ];

            $notificationid = $notificationmanager->create_notification(
                $recipient->id,
                'manual_alert',
                $subject,
                $message,
                $data->courseid ?? null,
                $channels,
                $metadata
            );

            if ($notificationid) {
                $notificationids[] = $notificationid;
            }
        }

        // Schedule reminders if requested.
        if (!empty($notificationids) && isset($data->eventdate)) {
            $this->schedule_reminders($notificationids[0], $data);
        }

        // Trigger event.
        $event = \local_student_monitor\event\alert_created::create([
            'objectid' => $notificationids[0] ?? 0,
            'context' => \context_system::instance(),
            'userid' => $USER->id,
            'other' => [
                'alerttype' => $data->alerttype,
                'recipients' => count($recipients),
            ],
        ]);
        $event->trigger();

        return count($notificationids);
    }

    /**
     * Get recipients based on selection.
     *
     * @param \stdClass $data Form data
     * @return array Array of user objects
     */
    protected function get_recipients($data) {
        global $DB;

        $recipients = [];

        switch ($data->recipients) {
            case 'all_course':
                if (empty($data->courseid)) {
                    return [];
                }
                $context = \context_course::instance($data->courseid);
                $recipients = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname, u.email');
                break;

            case 'group':
                if (empty($data->groupid)) {
                    return [];
                }
                $members = groups_get_members($data->groupid, 'u.id, u.firstname, u.lastname, u.email');
                $recipients = $members;
                break;

            case 'manual':
                if (empty($data->selectedusers)) {
                    return [];
                }
                $userids = explode(',', $data->selectedusers);
                foreach ($userids as $userid) {
                    $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email');
                    if ($user) {
                        $recipients[] = $user;
                    }
                }
                break;

            case 'all_students':
                // Get all students.
                $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                if ($studentrole) {
                    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                              FROM {user} u
                              JOIN {role_assignments} ra ON ra.userid = u.id
                             WHERE ra.roleid = :roleid
                               AND u.suspended = 0
                               AND u.deleted = 0";
                    $recipients = $DB->get_records_sql($sql, ['roleid' => $studentrole->id]);
                }
                break;
        }

        return $recipients;
    }

    /**
     * Schedule reminder notifications.
     *
     * @param int $basenotificationid Base notification ID
     * @param \stdClass $data Form data
     */
    protected function schedule_reminders($basenotificationid, $data) {
        global $DB;

        // Get base notification.
        $basenotification = $DB->get_record('local_sm_notifications', ['id' => $basenotificationid]);

        if (!$basenotification) {
            return;
        }

        $eventdate = $data->eventdate;
        $reminders = [];

        // Check which reminders are enabled.
        if (!empty($data->reminder_7days)) {
            $reminders[] = ['days' => 7, 'label' => 'J-7'];
        }
        if (!empty($data->reminder_3days)) {
            $reminders[] = ['days' => 3, 'label' => 'J-3'];
        }
        if (!empty($data->reminder_1day)) {
            $reminders[] = ['days' => 1, 'label' => 'J-1'];
        }

        // Create reminder notifications.
        foreach ($reminders as $reminder) {
            // Calculate when to send (event date - days).
            $reminderdate = $eventdate - ($reminder['days'] * 86400);

            // Only create if date is in the future.
            if ($reminderdate > time()) {
                // Clone the base notification.
                $remindernotif = clone $basenotification;
                unset($remindernotif->id);

                // Modify subject and message.
                $remindernotif->subject = '🔔 RAPPEL ' . $reminder['label'] . ' - ' . $remindernotif->subject;
                $remindernotif->message = "RAPPEL " . $reminder['label'] . "\n\n" . $remindernotif->message;
                $remindernotif->timecreated = $reminderdate;
                $remindernotif->status = 'pending';

                // Add metadata.
                $metadata = json_decode($remindernotif->metadata, true);
                $metadata['is_reminder'] = true;
                $metadata['reminder_days'] = $reminder['days'];
                $metadata['base_notification_id'] = $basenotificationid;
                $remindernotif->metadata = json_encode($metadata);

                $DB->insert_record('local_sm_notifications', $remindernotif);
            }
        }
    }

    /**
     * Get alert statistics.
     *
     * @param int $alertid Alert notification ID
     * @return \stdClass Statistics
     */
    public function get_alert_statistics($alertid) {
        global $DB;

        $stats = new \stdClass();

        // Get base notification.
        $notification = $DB->get_record('local_sm_notifications', ['id' => $alertid]);

        if (!$notification) {
            return $stats;
        }

        // Get metadata.
        $metadata = json_decode($notification->metadata, true);

        // Count related notifications (if this was sent to multiple users).
        $sql = "SELECT COUNT(*) as total,
                       SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                       SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount,
                       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                  FROM {local_sm_notifications}
                 WHERE type = 'manual_alert'
                   AND subject = :subject
                   AND timecreated BETWEEN :timestart AND :timeend";

        $params = [
            'subject' => $notification->subject,
            'timestart' => $notification->timecreated - 60,
            'timeend' => $notification->timecreated + 60,
        ];

        $stats = $DB->get_record_sql($sql, $params);

        return $stats;
    }

    /**
     * Get recent alerts.
     *
     * @param int $limit Number of alerts to return
     * @return array Array of alerts
     */
    public function get_recent_alerts($limit = 20) {
        global $DB;

        $sql = "SELECT DISTINCT n.subject, n.timecreated, n.sentby, u.firstname, u.lastname,
                       COUNT(n.id) as recipient_count
                  FROM {local_sm_notifications} n
                  JOIN {user} u ON u.id = n.sentby
                 WHERE n.type = 'manual_alert'
              GROUP BY n.subject, n.timecreated, n.sentby, u.firstname, u.lastname
              ORDER BY n.timecreated DESC";

        return $DB->get_records_sql($sql, [], 0, $limit);
    }
}
