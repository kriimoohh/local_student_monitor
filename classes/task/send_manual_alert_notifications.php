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
 * Adhoc task to send manual alert notifications in the background.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Sends the notifications created for a manual alert without blocking the
 * page that triggered the alert.
 */
class send_manual_alert_notifications extends \core\task\adhoc_task {

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $notificationids = $data->notificationids ?? [];

        if (empty($notificationids)) {
            return;
        }

        $channelmanager = new \local_student_monitor\manager\channel_manager();
        $notificationmanager = new \local_student_monitor\manager\notification_manager();

        foreach ($notificationids as $notificationid) {
            $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);

            if (!$notification || $notification->status !== 'pending') {
                continue;
            }

            $recipient = $DB->get_record('user', ['id' => $notification->userid]);

            if (!$recipient || $recipient->deleted || $recipient->suspended) {
                $notificationmanager->update_notification_status($notificationid, 'failed');
                continue;
            }

            $results = $channelmanager->send_notification($notification, $recipient);

            $success = false;
            foreach ($results as $result) {
                if ($result) {
                    $success = true;
                    break;
                }
            }

            $notificationmanager->update_notification_status($notificationid, $success ? 'sent' : 'failed');

            $event = \local_student_monitor\event\notification_sent::create([
                'objectid' => $notificationid,
                'context' => \context_system::instance(),
                'userid' => $recipient->id,
                'other' => [
                    'type' => $notification->type,
                    'success' => $success,
                ],
            ]);
            $event->trigger();
        }
    }
}
