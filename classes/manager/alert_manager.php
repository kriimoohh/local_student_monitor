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
     * @return array Array with 'count', 'success', and 'failed' keys
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
        if (isset($data->eventdate) && $data->eventdate > 0) {
            $message .= "\n\n";
            $message .= get_string('eventdate', 'local_student_monitor') . ': ' . userdate($data->eventdate);
        }

        if (isset($data->location) && !empty(trim($data->location))) {
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

        // Create and immediately send notifications for each recipient.
        $channelmanager = new channel_manager();
        $notificationids = [];
        $successcount = 0;
        $failcount = 0;

        foreach ($recipients as $recipient) {
            $metadata = [
                'alerttype' => $data->alerttype,
                'eventdate' => (isset($data->eventdate) && $data->eventdate > 0) ? $data->eventdate : null,
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

                // Send immediately for manual alerts.
                $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);
                if ($notification) {
                    $results = $channelmanager->send_notification($notification, $recipient);

                    // Check if at least one channel succeeded.
                    $success = false;
                    foreach ($results as $result) {
                        if ($result) {
                            $success = true;
                            break;
                        }
                    }

                    // Update notification status.
                    if ($success) {
                        $notificationmanager->update_notification_status($notificationid, 'sent');
                        $successcount++;
                    } else {
                        $notificationmanager->update_notification_status($notificationid, 'failed');
                        $failcount++;
                    }

                    // Trigger notification sent event.
                    $event = \local_student_monitor\event\notification_sent::create([
                        'objectid' => $notificationid,
                        'context' => \context_system::instance(),
                        'userid' => $recipient->id,
                        'other' => [
                            'type' => 'manual_alert',
                            'success' => $success,
                        ],
                    ]);
                    $event->trigger();
                }
            }
        }

        // Schedule reminders if requested.
        if (!empty($notificationids) && isset($data->eventdate) && $data->eventdate > 0) {
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
                'success' => $successcount,
                'failed' => $failcount,
            ],
        ]);
        $event->trigger();

        return [
            'count' => count($notificationids),
            'success' => $successcount,
            'failed' => $failcount,
        ];
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
            case 'by_inactivity_level':
                if (empty($data->inactivity_level)) {
                    return [];
                }

                // Get students based on selected inactivity level or risk level.
                $tracker = new student_tracker();

                if (strpos($data->inactivity_level, 'inactivity_level') === 0) {
                    // Handle inactivity levels (by days).
                    $thresholds = [
                        'inactivity_level1' => get_config('local_student_monitor', 'inactivity_threshold_1') ?: 3,
                        'inactivity_level2' => get_config('local_student_monitor', 'inactivity_threshold_2') ?: 7,
                        'inactivity_level3' => get_config('local_student_monitor', 'inactivity_threshold_3') ?: 14,
                    ];

                    $threshold = $thresholds[$data->inactivity_level] ?? 3;

                    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                              FROM {user} u
                              JOIN {local_sm_student_tracking} st ON st.userid = u.id
                             WHERE st.inactivity_days >= :threshold
                               AND u.deleted = 0
                               AND u.suspended = 0
                          ORDER BY st.inactivity_days DESC";

                    $recipients = $DB->get_records_sql($sql, ['threshold' => $threshold]);
                } else if (strpos($data->inactivity_level, 'risk_') === 0) {
                    // Handle risk levels.
                    $risklevelmap = [
                        'risk_critique' => 'CRITIQUE',
                        'risk_eleve' => 'ÉLEVÉ',
                        'risk_moyen' => 'MOYEN',
                        'risk_faible' => 'FAIBLE',
                    ];

                    $risklevel = $risklevelmap[$data->inactivity_level] ?? 'MOYEN';

                    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                              FROM {user} u
                              JOIN {local_sm_student_tracking} st ON st.userid = u.id
                             WHERE st.risk_level = :risklevel
                               AND u.deleted = 0
                               AND u.suspended = 0
                          ORDER BY st.inactivity_days DESC";

                    $recipients = $DB->get_records_sql($sql, ['risklevel' => $risklevel]);
                }
                break;

            case 'category':
                if (empty($data->categoryid)) {
                    return [];
                }
                // Get all courses in the category and its subcategories.
                $category = $DB->get_record('course_categories', ['id' => $data->categoryid]);
                if (!$category) {
                    return [];
                }

                // Get all courses in this category and subcategories.
                $categorypath = $category->path . '/%';
                $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                          FROM {user} u
                          JOIN {user_enrolments} ue ON ue.userid = u.id
                          JOIN {enrol} e ON e.id = ue.enrolid
                          JOIN {course} c ON c.id = e.courseid
                          JOIN {course_categories} cc ON cc.id = c.category
                         WHERE (cc.id = :categoryid OR " . $DB->sql_like('cc.path', ':categorypath') . ")
                           AND u.deleted = 0
                           AND u.suspended = 0
                           AND ue.status = 0
                           AND e.status = 0
                           AND c.id != :siteid";

                $params = [
                    'categoryid' => $data->categoryid,
                    'categorypath' => $categorypath,
                    'siteid' => SITEID
                ];

                $recipients = $DB->get_records_sql($sql, $params);
                break;

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
                // Handle both array (from autocomplete) and comma-separated string formats.
                $userids = is_array($data->selectedusers) ? $data->selectedusers : explode(',', $data->selectedusers);
                foreach ($userids as $userid) {
                    $userid = trim($userid);
                    if (empty($userid)) {
                        continue;
                    }
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

            case 'csv':
                // Get recipients from CSV file.
                if (isset($data->csvfile) && !empty($data->csvfile)) {
                    $recipients = $this->process_csv_file($data->csvfile);
                }
                break;
        }

        return $recipients;
    }

    /**
     * Process CSV file and extract recipients.
     *
     * @param int $draftitemid Draft file area item ID
     * @return array Array of user objects
     */
    protected function process_csv_file($draftitemid) {
        global $DB, $USER;

        $recipients = [];
        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);

        // Get the file from the draft area.
        $files = $fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id DESC', false);

        if (empty($files)) {
            return $recipients;
        }

        $file = reset($files);
        $content = $file->get_content();

        // Parse CSV content.
        $lines = str_getcsv($content, "\n");
        $processedusers = [];

        foreach ($lines as $line) {
            // Skip empty lines.
            if (empty(trim($line))) {
                continue;
            }

            // Parse CSV line.
            $data = str_getcsv($line, ',');

            if (empty($data[0])) {
                continue;
            }

            $identifier = trim($data[0]);

            // Skip if already processed.
            if (in_array($identifier, $processedusers)) {
                continue;
            }

            // Try to find user by email, username, or ID.
            $user = null;

            // Check if it's an email.
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $user = $DB->get_record('user', ['email' => $identifier, 'deleted' => 0],
                    'id, firstname, lastname, email');
            } else if (is_numeric($identifier)) {
                // Check if it's a user ID.
                $user = $DB->get_record('user', ['id' => $identifier, 'deleted' => 0],
                    'id, firstname, lastname, email');
            } else {
                // Try as username.
                $user = $DB->get_record('user', ['username' => $identifier, 'deleted' => 0],
                    'id, firstname, lastname, email');
            }

            if ($user) {
                $recipients[] = $user;
                $processedusers[] = $identifier;
            }
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
     * Get recipients for preview (public wrapper for get_recipients).
     *
     * @param \stdClass $data Form data
     * @return array Array of user objects
     */
    public function get_recipients_for_preview($data) {
        return $this->get_recipients($data);
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

        // Get all alerts (manual and automatic) with sender information.
        // Group by subject and timecreated to show unique alert campaigns.
        $sql = "SELECT n.id, n.subject, n.timecreated, n.sentby, n.type, n.status,
                       u.firstname, u.lastname,
                       COUNT(DISTINCT n2.id) as recipient_count
                  FROM {local_sm_notifications} n
                  LEFT JOIN {user} u ON u.id = n.sentby
                  LEFT JOIN {local_sm_notifications} n2 ON (
                      n2.subject = n.subject
                      AND ABS(n2.timecreated - n.timecreated) < 60
                      AND n2.type = n.type
                  )
                 WHERE n.status IN ('sent', 'pending', 'failed')
                   AND n.id = (
                       SELECT MIN(n3.id)
                       FROM {local_sm_notifications} n3
                       WHERE n3.subject = n.subject
                         AND ABS(n3.timecreated - n.timecreated) < 60
                         AND n3.type = n.type
                   )
              GROUP BY n.id, n.subject, n.timecreated, n.sentby, n.type, n.status, u.firstname, u.lastname
              ORDER BY n.timecreated DESC";

        return $DB->get_records_sql($sql, [], 0, $limit);
    }
}
