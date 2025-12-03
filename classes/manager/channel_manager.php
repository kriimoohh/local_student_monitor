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
 * @copyright  2025 UNCHK - Universite Numerique Cheikh Hamidou Kane
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

    /** @var int Rate limit: max SMS per user per hour */
    const RATE_LIMIT_SMS_PER_HOUR = 5;

    /** @var int Rate limit: max WhatsApp per user per hour */
    const RATE_LIMIT_WHATSAPP_PER_HOUR = 10;

    /** @var int Rate limit window in seconds */
    const RATE_LIMIT_WINDOW = 3600;

    /** @var int Maximum retry attempts for failed sends */
    const MAX_RETRY_ATTEMPTS = 3;

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

            try {
                switch ($channel) {
                    case 'email':
                        $results['email'] = $this->send_email($user, $notification->subject, $notification->message);
                        break;

                    case 'moodle':
                        $fromuserid = isset($notification->sentby) ? $notification->sentby : null;
                        $results['moodle'] = $this->send_moodle_notification(
                            $user,
                            $notification->subject,
                            $notification->message,
                            $fromuserid
                        );
                        break;

                    case 'sms':
                        if (get_config('local_student_monitor', 'channel_sms')) {
                            $phone = $this->get_validated_phone($user);
                            if ($phone) {
                                if ($this->check_rate_limit($user->id, 'sms')) {
                                    $results['sms'] = $this->send_sms($phone, $notification->message);
                                } else {
                                    $results['sms'] = ['success' => false, 'error' => 'rate_limit_exceeded'];
                                    $this->log_rate_limit_exceeded($user->id, 'sms');
                                }
                            } else {
                                $results['sms'] = ['success' => false, 'error' => 'invalid_phone'];
                            }
                        }
                        break;

                    case 'whatsapp':
                        if (get_config('local_student_monitor', 'channel_whatsapp')) {
                            $phone = $this->get_validated_phone($user);
                            if ($phone) {
                                if ($this->check_rate_limit($user->id, 'whatsapp')) {
                                    $results['whatsapp'] = $this->send_whatsapp($phone, $notification->message);
                                } else {
                                    $results['whatsapp'] = ['success' => false, 'error' => 'rate_limit_exceeded'];
                                    $this->log_rate_limit_exceeded($user->id, 'whatsapp');
                                }
                            } else {
                                $results['whatsapp'] = ['success' => false, 'error' => 'invalid_phone'];
                            }
                        }
                        break;
                }
            } catch (\Exception $e) {
                $results[$channel] = ['success' => false, 'error' => $e->getMessage()];
                $this->log_channel_error($channel, $user->id, $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Validate and get phone number from user.
     *
     * @param \stdClass $user User object
     * @return string|null Validated phone number or null if invalid
     */
    protected function get_validated_phone($user) {
        $phone = $user->phone1 ?? $user->phone2 ?? null;

        if (!$phone) {
            return null;
        }

        // Validate phone number format.
        if (!$this->validate_phone_number($phone)) {
            return null;
        }

        return $this->normalize_phone_number($phone);
    }

    /**
     * Validate phone number format.
     *
     * @param string $phone Phone number
     * @return bool True if valid
     */
    public function validate_phone_number($phone) {
        // Remove all non-numeric characters except +.
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Check minimum length (international format).
        if (strlen($cleaned) < 8) {
            return false;
        }

        // Check maximum length.
        if (strlen($cleaned) > 15) {
            return false;
        }

        // Should start with + or a digit.
        if (!preg_match('/^[+0-9]/', $cleaned)) {
            return false;
        }

        return true;
    }

    /**
     * Normalize phone number to international format.
     *
     * @param string $phone Phone number
     * @return string Normalized phone number
     */
    public function normalize_phone_number($phone) {
        // Remove all non-numeric characters except +.
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // If it doesn't start with +, assume it needs country code.
        // Default to Senegal (+221) if no country code.
        if (!str_starts_with($cleaned, '+')) {
            // Remove leading 0 if present.
            $cleaned = ltrim($cleaned, '0');
            // Add Senegal country code.
            $cleaned = '+221' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Check rate limit for a channel.
     *
     * @param int $userid User ID
     * @param string $channel Channel name
     * @return bool True if within rate limit
     */
    protected function check_rate_limit($userid, $channel) {
        global $DB;

        $limit = ($channel === 'sms') ? self::RATE_LIMIT_SMS_PER_HOUR : self::RATE_LIMIT_WHATSAPP_PER_HOUR;
        $since = time() - self::RATE_LIMIT_WINDOW;

        $sql = "SELECT COUNT(*) as count
                  FROM {local_sm_logs}
                 WHERE action = :action
                   AND userid = :userid
                   AND timecreated > :since";

        $result = $DB->get_record_sql($sql, [
            'action' => $channel . '_sent',
            'userid' => $userid,
            'since' => $since,
        ]);

        return ($result->count ?? 0) < $limit;
    }

    /**
     * Log rate limit exceeded event.
     *
     * @param int $userid User ID
     * @param string $channel Channel name
     */
    protected function log_rate_limit_exceeded($userid, $channel) {
        global $DB;

        $log = new \stdClass();
        $log->action = $channel . '_rate_limit_exceeded';
        $log->userid = $userid;
        $log->targetid = null;
        $log->details = json_encode(['channel' => $channel, 'window' => self::RATE_LIMIT_WINDOW]);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Log channel error.
     *
     * @param string $channel Channel name
     * @param int $userid User ID
     * @param string $error Error message
     */
    protected function log_channel_error($channel, $userid, $error) {
        global $DB;

        $log = new \stdClass();
        $log->action = $channel . '_error';
        $log->userid = $userid;
        $log->targetid = null;
        $log->details = json_encode(['error' => substr($error, 0, 500)]);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
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
        try {
            // Get the from user (noreply).
            $from = \core_user::get_noreply_user();

            // Convert plain text message to HTML.
            $messagehtml = text_to_html($message);

            // Send email.
            return email_to_user($user, $from, $subject, $message, $messagehtml);
        } catch (\Exception $e) {
            debugging('Error sending email: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
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

        try {
            // Get the sender user - use the specified user, or fall back to support user.
            if ($fromuserid) {
                $userfrom = $DB->get_record('user', ['id' => $fromuserid]);
                if (!$userfrom) {
                    $userfrom = \core_user::get_support_user();
                }
            } else {
                $userfrom = \core_user::get_support_user();
            }

            // Use Moodle's direct messaging API instead of notification system.
            // This sends the message to the user's message inbox.
            $messageid = message_post_message($userfrom, $user, $message, FORMAT_PLAIN);
            return $messageid;
        } catch (\Exception $e) {
            debugging('Error sending Moodle message: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
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

        // Clean phone number.
        $phone = $this->normalize_phone_number($phone);

        // Keep original message for tracking.
        $originalmessage = $message;

        // Prepare POST data.
        $postdata = [
            'to' => $phone,
            'message' => $message,
            'api_key' => $apikey,
        ];

        $attempts = 0;
        $success = false;

        while ($attempts < self::MAX_RETRY_ATTEMPTS && !$success) {
            $attempts++;

            try {
                // Use cURL to send SMS.
                $ch = curl_init($apiurl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlerror = curl_error($ch);
                curl_close($ch);

                if ($curlerror) {
                    throw new \Exception('cURL error: ' . $curlerror);
                }

                // Log the SMS send attempt.
                $this->log_sms_send($phone, $message, $httpcode, $response);

                // Track SMS cost if successful.
                if ($httpcode == 200 && $notificationid > 0) {
                    $smstracker->track_sms($notificationid, $phone, $originalmessage);
                }

                $success = ($httpcode == 200);

            } catch (\Exception $e) {
                debugging('Error sending SMS (attempt ' . $attempts . '): ' . $e->getMessage(), DEBUG_DEVELOPER);

                if ($attempts < self::MAX_RETRY_ATTEMPTS) {
                    // Wait before retry (exponential backoff).
                    usleep(pow(2, $attempts) * 100000); // 200ms, 400ms, 800ms...
                }
            }
        }

        return $success;
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

        // Clean and normalize phone number.
        $phone = preg_replace('/[^0-9]/', '', $this->normalize_phone_number($phone));

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

        $attempts = 0;
        $success = false;

        while ($attempts < self::MAX_RETRY_ATTEMPTS && !$success) {
            $attempts++;

            try {
                $ch = curl_init($apiurl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                ]);

                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlerror = curl_error($ch);
                curl_close($ch);

                if ($curlerror) {
                    throw new \Exception('cURL error: ' . $curlerror);
                }

                // Log the WhatsApp send attempt.
                $this->log_whatsapp_send($phone, $message, $httpcode, $response);

                $success = ($httpcode == 200);

            } catch (\Exception $e) {
                debugging('Error sending WhatsApp (attempt ' . $attempts . '): ' . $e->getMessage(), DEBUG_DEVELOPER);

                if ($attempts < self::MAX_RETRY_ATTEMPTS) {
                    // Wait before retry (exponential backoff).
                    usleep(pow(2, $attempts) * 100000);
                }
            }
        }

        return $success;
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
        $phone = preg_replace('/[^0-9]/', '', $this->normalize_phone_number($phone));

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
            'phone' => $this->mask_phone_number($phone),
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
            'phone' => $this->mask_phone_number($phone),
            'message_length' => strlen($message),
            'http_code' => $httpcode,
            'response' => substr($response, 0, 500),
        ]);
        $log->timecreated = time();
        $log->ip = getremoteaddr();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Mask phone number for logging (privacy).
     *
     * @param string $phone Phone number
     * @return string Masked phone number
     */
    protected function mask_phone_number($phone) {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($phone, 0, 4) . str_repeat('*', $length - 8) . substr($phone, -4);
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

        try {
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
                    $phone = $this->get_validated_phone($user);
                    if ($phone) {
                        $result['success'] = $this->send_sms($phone, $testmessage);
                        $result['message'] = $result['success'] ? 'SMS sent successfully' : 'Failed to send SMS';
                    } else {
                        $result['message'] = 'No valid phone number configured for user';
                    }
                    break;

                case 'whatsapp':
                    $phone = $this->get_validated_phone($user);
                    if ($phone) {
                        $result['success'] = $this->send_whatsapp($phone, $testmessage);
                        $result['message'] = $result['success'] ? 'WhatsApp sent successfully' : 'Failed to send WhatsApp';
                    } else {
                        $result['message'] = 'No valid phone number configured for user';
                    }
                    break;
            }
        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
        }

        return $result;
    }
}
