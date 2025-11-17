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
 * Scheduled task to send pending notifications.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to send pending/scheduled notifications through configured channels.
 */
class send_scheduled_notifications extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_send_scheduled_notifications', 'local_student_monitor');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (!get_config('local_student_monitor', 'enabled')) {
            mtrace('Student Monitor plugin is disabled. Skipping notification sending.');
            return;
        }

        mtrace('Starting to send pending notifications...');

        // Get all pending notifications (limit to 100 per run to avoid timeout).
        $notifications = $DB->get_records('local_sm_notifications', ['status' => 'pending'], 'timecreated ASC', '*', 0, 100);

        mtrace('Found ' . count($notifications) . ' pending notifications to send.');

        $channelmanager = new \local_student_monitor\manager\channel_manager();
        $notificationmanager = new \local_student_monitor\manager\notification_manager();

        $successcount = 0;
        $failcount = 0;

        foreach ($notifications as $notification) {
            // Get user.
            $user = $DB->get_record('user', ['id' => $notification->userid]);

            if (!$user || $user->deleted || $user->suspended) {
                // User not valid, mark notification as failed.
                $notificationmanager->update_notification_status($notification->id, 'failed');
                mtrace("  Notification {$notification->id}: User invalid or deleted");
                $failcount++;
                continue;
            }

            mtrace("  Sending notification {$notification->id} to user {$user->id} ({$user->email})...");

            // Send through all configured channels.
            $results = $channelmanager->send_notification($notification, $user);

            // Check if at least one channel succeeded.
            $success = false;
            foreach ($results as $channel => $result) {
                if ($result) {
                    mtrace("    {$channel}: SUCCESS");
                    $success = true;
                } else {
                    mtrace("    {$channel}: FAILED");
                }
            }

            // Update notification status.
            if ($success) {
                $notificationmanager->update_notification_status($notification->id, 'sent');
                $successcount++;
            } else {
                $notificationmanager->update_notification_status($notification->id, 'failed');
                $failcount++;
            }

            // Trigger event.
            $event = \local_student_monitor\event\notification_sent::create([
                'objectid' => $notification->id,
                'context' => \context_system::instance(),
                'userid' => $user->id,
                'other' => [
                    'type' => $notification->type,
                    'success' => $success,
                ],
            ]);
            $event->trigger();
        }

        mtrace('Notification sending complete.');
        mtrace("  Successfully sent: {$successcount}");
        mtrace("  Failed: {$failcount}");
    }
}
