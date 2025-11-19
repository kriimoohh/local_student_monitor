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
 * Channel manager class for handling different notification channels.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');

/**
 * Class channel_manager
 *
 * Manages sending notifications through different channels.
 */
class channel_manager {

    /**
     * Send a notification through specified channels.
     *
     * @param \stdClass $notification Notification record
     * @param \stdClass $user User object
     * @return array Results for each channel
     */
    public function send_notification($notification, $user) {
        $channels = explode(',', $notification->channels);
        $results = [];

        foreach ($channels as $channel) {
            $channel = trim($channel);

            switch ($channel) {
                case 'email':
                    $results['email'] = $this->send_email($user, $notification->subject, $notification->message);
                    break;

                case 'moodle':
                    $fromuserid = isset($notification->sentby) ? $notification->sentby : null;
                    $results['moodle'] = $this->send_moodle_notification($user, $notification->subject, $notification->message, $fromuserid);
                    break;

                case 'sms':
                    if (get_config('local_student_monitor', 'channel_sms')) {
                        $phone = $user->phone1 ?? $user->phone2 ?? null;
                        if ($phone) {
                            $results['sms'] = $this->send_sms($phone, $notification->message);
                        } else {
                            $results['sms'] = false;
                        }
                    }
                    break;

                case 'whatsapp':
                    if (get_config('local_student_monitor', 'channel_whatsapp')) {
                        $phone = $user->phone1 ?? $user->phone2 ?? null;
                        if ($phone) {
                            $results['whatsapp'] = $this->send_whatsapp($phone, $notification->message);
                        } else {
                            $results['whatsapp'] = false;
                        }
                    }
                    break;
            }
        }

        return $results;
    }

    /**
     * Send email notification.
     *
     * @param \stdClass $user User object
     * @param string $subject Email subject
     * @param string $message Email message (plain text)
     * @return bool Success
     */
    public function send_email($user, $subject, $message) {
        global $CFG;

        // Get the from user (noreply).
        $from = \core_user::get_noreply_user();

        // Convert plain text message to HTML.
        $messagehtml = text_to_html($message);

        // Send email.
        return email_to_user($user, $from, $subject, $message, $messagehtml);
    }

    /**
     * Send Moodle notification.
     *
     * @param \stdClass $user User object
     * @param string $subject Subject
     * @param string $message Message
     * @param int|null $fromuserid User ID of sender (defaults to support user)
     * @return int|false Message ID or false on failure
     */
    public function send_moodle_notification($user, $subject, $message, $fromuserid = null) {
        global $DB;

        // Get the sender user - use the specified user, or fall back to support user.
        if ($fromuserid) {
            $userfrom = $DB->get_record('user', ['id' => $fromuserid]);
            if (!$userfrom) {
                $userfrom = \core_user::get_support_user();
            }
        } else {
            $userfrom = \core_user::get_support_user();
        }

        // Create message object for direct messaging (not notification).
        $messagecontent = new \core\message\message();
        $messagecontent->component = 'moodle';
        $messagecontent->name = 'instantmessage';
        $messagecontent->userfrom = $userfrom;
        $messagecontent->userto = $user;
        $messagecontent->subject = $subject;
        $messagecontent->fullmessage = $message;
        $messagecontent->fullmessageformat = FORMAT_PLAIN;
        $messagecontent->fullmessagehtml = text_to_html($message);
        $messagecontent->smallmessage = $subject;
        $messagecontent->notification = 0; // This is a direct message, not a notification.

        return message_send($messagecontent);
    }

    /**
     * Send SMS notification.
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @param int $notificationid Notification ID for cost tracking
     * @return bool Success
     */
    public function send_sms($phone, $message, $notificationid = 0) {
        $apiurl = get_config('local_student_monitor', 'sms_api_url');
        $apikey = get_config('local_student_monitor', 'sms_api_key');

        if (empty($apiurl) || empty($apikey)) {
            debugging('SMS API not configured', DEBUG_DEVELOPER);
            return false;
        }

        // Check if budget limit is reached.
        $smstracker = new sms_cost_tracker();
        if ($smstracker->is_budget_limit_reached()) {
            debugging('SMS budget limit reached for this month', DEBUG_DEVELOPER);
            return false;
        }

        // Clean phone number (remove spaces, dashes, etc.).
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Don't truncate - allow multiple SMS parts.
        $originalmessage = $message;

        // Prepare POST data.
        $postdata = [
            'to' => $phone,
            'message' => $message,
            'api_key' => $apikey,
        ];

        try {
            // Use cURL to send SMS.
            $ch = curl_init($apiurl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log the SMS send attempt.
            $this->log_sms_send($phone, $message, $httpcode, $response);

            // Track SMS cost if successful.
            if ($httpcode == 200 && $notificationid > 0) {
                $smstracker->track_sms($notificationid, $phone, $originalmessage);
            }

            return $httpcode == 200;
        } catch (\Exception $e) {
            debugging('Error sending SMS: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Send WhatsApp notification.
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return bool Success
     */
    public function send_whatsapp($phone, $message) {
        $phoneid = get_config('local_student_monitor', 'whatsapp_phone_id');
        $token = get_config('local_student_monitor', 'whatsapp_token');

        if (empty($phoneid) || empty($token)) {
            debugging('WhatsApp API not configured', DEBUG_DEVELOPER);
            return false;
        }

        // Clean phone number.
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // WhatsApp Business API endpoint.
        $apiurl = "https://graph.facebook.com/v18.0/{$phoneid}/messages";

        // Prepare message data.
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        try {
            $ch = curl_init($apiurl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log the WhatsApp send attempt.
            $this->log_whatsapp_send($phone, $message, $httpcode, $response);

            return $httpcode == 200;
        } catch (\Exception $e) {
            debugging('Error sending WhatsApp: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Send WhatsApp template message.
     *
     * @param string $phone Phone number
     * @param string $templatename Template name (pre-approved in WhatsApp)
     * @param array $parameters Template parameters
     * @return bool Success
     */
    public function send_whatsapp_template($phone, $templatename, $parameters = []) {
        $phoneid = get_config('local_student_monitor', 'whatsapp_phone_id');
        $token = get_config('local_student_monitor', 'whatsapp_token');

        if (empty($phoneid) || empty($token)) {
            debugging('WhatsApp API not configured', DEBUG_DEVELOPER);
            return false;
        }

        // Clean phone number.
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // WhatsApp Business API endpoint.
        $apiurl = "https://graph.facebook.com/v18.0/{$phoneid}/messages";

        // Prepare template parameters.
        $templateparams = [];
        foreach ($parameters as $param) {
            $templateparams[] = [
                'type' => 'text',
                'text' => $param
            ];
        }

        // Prepare template message data.
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templatename,
                'language' => [
                    'code' => 'fr' // French for UNCHK Senegal
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $templateparams
                    ]
                ]
            ],
        ];

        try {
            $ch = curl_init($apiurl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log the WhatsApp send attempt.
            $this->log_whatsapp_send($phone, 'Template: ' . $templatename, $httpcode, $response);

            return $httpcode == 200;
        } catch (\Exception $e) {
            debugging('Error sending WhatsApp template: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Log SMS send attempt.
     *
     * @param string $phone Phone number
     * @param string $message Message
     * @param int $httpcode HTTP response code
     * @param string $response API response
     */
    protected function log_sms_send($phone, $message, $httpcode, $response) {
        global $DB;

        $log = new \stdClass();
        $log->action = 'sms_sent';
        $log->userid = null;
        $log->targetid = null;
        $log->details = json_encode([
            'phone' => $phone,
            'message_length' => strlen($message),
            'http_code' => $httpcode,
            'response' => substr($response, 0, 500),
        ]);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Log WhatsApp send attempt.
     *
     * @param string $phone Phone number
     * @param string $message Message
     * @param int $httpcode HTTP response code
     * @param string $response API response
     */
    protected function log_whatsapp_send($phone, $message, $httpcode, $response) {
        global $DB;

        $log = new \stdClass();
        $log->action = 'whatsapp_sent';
        $log->userid = null;
        $log->targetid = null;
        $log->details = json_encode([
            'phone' => $phone,
            'message_length' => strlen($message),
            'http_code' => $httpcode,
            'response' => substr($response, 0, 500),
        ]);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Test channel configuration.
     *
     * @param string $channel Channel to test (email, moodle, sms, whatsapp)
     * @param \stdClass $user Test user
     * @return array Test result
     */
    public function test_channel($channel, $user) {
        $result = [
            'channel' => $channel,
            'success' => false,
            'message' => '',
        ];

        $testsubject = 'Test Student Monitor Notification';
        $testmessage = 'This is a test notification from Student Monitor plugin.';

        switch ($channel) {
            case 'email':
                $result['success'] = $this->send_email($user, $testsubject, $testmessage);
                $result['message'] = $result['success'] ? 'Email sent successfully' : 'Failed to send email';
                break;

            case 'moodle':
                global $USER;
                $result['success'] = (bool) $this->send_moodle_notification($user, $testsubject, $testmessage, $USER->id);
                $result['message'] = $result['success'] ? 'Moodle message sent' : 'Failed to send Moodle message';
                break;

            case 'sms':
                $phone = $user->phone1 ?? null;
                if ($phone) {
                    $result['success'] = $this->send_sms($phone, $testmessage);
                    $result['message'] = $result['success'] ? 'SMS sent successfully' : 'Failed to send SMS';
                } else {
                    $result['message'] = 'No phone number configured for user';
                }
                break;

            case 'whatsapp':
                $phone = $user->phone1 ?? null;
                if ($phone) {
                    $result['success'] = $this->send_whatsapp($phone, $testmessage);
                    $result['message'] = $result['success'] ? 'WhatsApp sent successfully' : 'Failed to send WhatsApp';
                } else {
                    $result['message'] = 'No phone number configured for user';
                }
                break;
        }

        return $result;
    }
}
