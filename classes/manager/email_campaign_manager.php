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
 * Email campaign manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Email campaign manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class email_campaign_manager {

    /**
     * Create a new email campaign.
     *
     * @param string $name Campaign name
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $targetcriteria Target audience criteria
     * @param int $scheduledtime Scheduled send time (0 = send now)
     * @param bool $abtesting Enable A/B testing
     * @return int Campaign ID
     */
    public function create_campaign($name, $subject, $message, $targetcriteria, $scheduledtime = 0, $abtesting = false) {
        global $DB, $USER;

        $campaign = new \stdClass();
        $campaign->campaign_name = $name;
        $campaign->subject = $subject;
        $campaign->message = $message;
        $campaign->target_criteria = json_encode($targetcriteria);
        $campaign->scheduled_time = $scheduledtime > 0 ? $scheduledtime : time();
        $campaign->status = $scheduledtime > time() ? 'scheduled' : 'draft';
        $campaign->ab_testing = $abtesting ? 1 : 0;
        $campaign->created_by = $USER->id;
        $campaign->timecreated = time();
        $campaign->timemodified = time();

        if ($DB->get_manager()->table_exists('local_sm_campaigns')) {
            return $DB->insert_record('local_sm_campaigns', $campaign);
        } else {
            // Fallback to config table.
            $config = new \stdClass();
            $config->courseid = 0;
            $config->config_type = 'email_campaign';
            $config->config_key = 'campaign_' . time();
            $config->config_value = json_encode($campaign);
            $config->timecreated = time();
            return $DB->insert_record('local_sm_config', $config);
        }
    }

    /**
     * Get target audience for a campaign.
     *
     * @param array $criteria Target criteria
     * @return array User IDs
     */
    public function get_target_audience($criteria) {
        global $DB;

        $sql = "SELECT DISTINCT st.userid
                FROM {local_sm_student_tracking} st
                JOIN {user} u ON u.id = st.userid
                WHERE 1=1";
        $params = [];

        // Apply risk level filter.
        if (isset($criteria['risk_levels']) && !empty($criteria['risk_levels'])) {
            list($insql, $inparams) = $DB->get_in_or_equal($criteria['risk_levels'], SQL_PARAMS_NAMED);
            $sql .= " AND st.risk_level $insql";
            $params = array_merge($params, $inparams);
        }

        // Apply inactivity filter.
        if (isset($criteria['inactivity_min'])) {
            $sql .= " AND st.inactivity_days >= :inactivitymin";
            $params['inactivitymin'] = $criteria['inactivity_min'];
        }

        if (isset($criteria['inactivity_max'])) {
            $sql .= " AND st.inactivity_days <= :inactivitymax";
            $params['inactivitymax'] = $criteria['inactivity_max'];
        }

        // Apply missing assignments filter.
        if (isset($criteria['missing_min'])) {
            $sql .= " AND st.missing_activities >= :missingmin";
            $params['missingmin'] = $criteria['missing_min'];
        }

        // Apply assigned status filter.
        if (isset($criteria['assigned_status'])) {
            if ($criteria['assigned_status'] === 'assigned') {
                $sql .= " AND st.assigned_to IS NOT NULL";
            } else if ($criteria['assigned_status'] === 'unassigned') {
                $sql .= " AND st.assigned_to IS NULL";
            }
        }

        // Apply course filter.
        if (isset($criteria['course_id']) && $criteria['course_id'] > 0) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid = u.id AND e.courseid = :courseid
            )";
            $params['courseid'] = $criteria['course_id'];
        }

        $results = $DB->get_records_sql($sql, $params);
        return array_keys($results);
    }

    /**
     * Send campaign to target audience.
     *
     * @param int $campaignid Campaign ID
     * @return object Send statistics
     */
    public function send_campaign($campaignid) {
        global $DB;

        $campaign = $DB->get_record('local_sm_campaigns', ['id' => $campaignid]);
        if (!$campaign) {
            return false;
        }

        $criteria = json_decode($campaign->target_criteria, true);
        $recipients = $this->get_target_audience($criteria);

        $stats = new \stdClass();
        $stats->total_recipients = count($recipients);
        $stats->sent = 0;
        $stats->failed = 0;
        $stats->start_time = time();

        // Handle A/B testing.
        if ($campaign->ab_testing) {
            $this->send_ab_test_campaign($campaign, $recipients, $stats);
        } else {
            $this->send_standard_campaign($campaign, $recipients, $stats);
        }

        // Update campaign status.
        $campaign->status = 'sent';
        $campaign->sent_at = time();
        $campaign->recipients_count = $stats->total_recipients;
        $campaign->sent_count = $stats->sent;
        $campaign->failed_count = $stats->failed;
        $campaign->timemodified = time();

        $DB->update_record('local_sm_campaigns', $campaign);

        $stats->end_time = time();
        $stats->duration = $stats->end_time - $stats->start_time;

        // Log campaign send.
        $this->log_campaign_send($campaignid, $stats);

        return $stats;
    }

    /**
     * Send standard campaign.
     *
     * @param object $campaign Campaign record
     * @param array $recipients User IDs
     * @param object $stats Statistics object
     */
    protected function send_standard_campaign($campaign, $recipients, &$stats) {
        global $DB;

        $notificationmanager = new notification_manager();

        foreach ($recipients as $userid) {
            try {
                $user = $DB->get_record('user', ['id' => $userid]);
                if (!$user) {
                    $stats->failed++;
                    continue;
                }

                // Replace placeholders in subject and message.
                $subject = $notificationmanager->replace_placeholders($campaign->subject, $user);
                $message = $notificationmanager->replace_placeholders($campaign->message, $user);

                // Send email.
                $from = \core_user::get_noreply_user();
                if (email_to_user($user, $from, $subject, $message, $message)) {
                    $stats->sent++;

                    // Track campaign recipient.
                    $this->track_campaign_recipient($campaign->id, $userid, 'A', 'sent');
                } else {
                    $stats->failed++;
                    $this->track_campaign_recipient($campaign->id, $userid, 'A', 'failed');
                }
            } catch (\Exception $e) {
                $stats->failed++;
            }
        }
    }

    /**
     * Send A/B test campaign.
     *
     * @param object $campaign Campaign record
     * @param array $recipients User IDs
     * @param object $stats Statistics object
     */
    protected function send_ab_test_campaign($campaign, $recipients, &$stats) {
        global $DB;

        // Get A/B test variants.
        $variants = $this->get_ab_variants($campaign->id);
        if (empty($variants)) {
            // No variants, send as standard.
            $this->send_standard_campaign($campaign, $recipients, $stats);
            return;
        }

        // Split recipients into groups.
        $groups = $this->split_recipients_for_ab($recipients, count($variants) + 1);

        $notificationmanager = new notification_manager();

        // Send control group (original).
        foreach ($groups[0] as $userid) {
            try {
                $user = $DB->get_record('user', ['id' => $userid]);
                if (!$user) {
                    $stats->failed++;
                    continue;
                }

                $subject = $notificationmanager->replace_placeholders($campaign->subject, $user);
                $message = $notificationmanager->replace_placeholders($campaign->message, $user);

                $from = \core_user::get_noreply_user();
                if (email_to_user($user, $from, $subject, $message, $message)) {
                    $stats->sent++;
                    $this->track_campaign_recipient($campaign->id, $userid, 'control', 'sent');
                } else {
                    $stats->failed++;
                    $this->track_campaign_recipient($campaign->id, $userid, 'control', 'failed');
                }
            } catch (\Exception $e) {
                $stats->failed++;
            }
        }

        // Send variant groups.
        foreach ($variants as $index => $variant) {
            $groupindex = $index + 1;
            foreach ($groups[$groupindex] as $userid) {
                try {
                    $user = $DB->get_record('user', ['id' => $userid]);
                    if (!$user) {
                        $stats->failed++;
                        continue;
                    }

                    $subject = $notificationmanager->replace_placeholders($variant->subject, $user);
                    $message = $notificationmanager->replace_placeholders($variant->message, $user);

                    $from = \core_user::get_noreply_user();
                    if (email_to_user($user, $from, $subject, $message, $message)) {
                        $stats->sent++;
                        $this->track_campaign_recipient($campaign->id, $userid, 'variant_' . $variant->id, 'sent');
                    } else {
                        $stats->failed++;
                        $this->track_campaign_recipient($campaign->id, $userid, 'variant_' . $variant->id, 'failed');
                    }
                } catch (\Exception $e) {
                    $stats->failed++;
                }
            }
        }
    }

    /**
     * Split recipients for A/B testing.
     *
     * @param array $recipients User IDs
     * @param int $groups Number of groups
     * @return array Groups of user IDs
     */
    protected function split_recipients_for_ab($recipients, $groups) {
        $result = array_fill(0, $groups, []);
        shuffle($recipients);

        foreach ($recipients as $index => $userid) {
            $group = $index % $groups;
            $result[$group][] = $userid;
        }

        return $result;
    }

    /**
     * Track campaign recipient.
     *
     * @param int $campaignid Campaign ID
     * @param int $userid User ID
     * @param string $variant Variant identifier
     * @param string $status Send status
     */
    protected function track_campaign_recipient($campaignid, $userid, $variant, $status) {
        global $DB;

        $tracking = new \stdClass();
        $tracking->campaign_id = $campaignid;
        $tracking->user_id = $userid;
        $tracking->variant = $variant;
        $tracking->status = $status;
        $tracking->sent_at = time();
        $tracking->opened_at = null;
        $tracking->clicked_at = null;

        if ($DB->get_manager()->table_exists('local_sm_campaign_tracking')) {
            $DB->insert_record('local_sm_campaign_tracking', $tracking);
        }
    }

    /**
     * Get A/B test variants for a campaign.
     *
     * @param int $campaignid Campaign ID
     * @return array Variant records
     */
    protected function get_ab_variants($campaignid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_ab_variants')) {
            return $DB->get_records('local_sm_ab_variants', ['campaign_id' => $campaignid]);
        }
        return [];
    }

    /**
     * Create A/B test variant.
     *
     * @param int $campaignid Campaign ID
     * @param string $name Variant name
     * @param string $subject Email subject
     * @param string $message Email message
     * @return int Variant ID
     */
    public function create_ab_variant($campaignid, $name, $subject, $message) {
        global $DB;

        $variant = new \stdClass();
        $variant->campaign_id = $campaignid;
        $variant->variant_name = $name;
        $variant->subject = $subject;
        $variant->message = $message;
        $variant->timecreated = time();

        if ($DB->get_manager()->table_exists('local_sm_ab_variants')) {
            return $DB->insert_record('local_sm_ab_variants', $variant);
        }
        return 0;
    }

    /**
     * Get campaign statistics.
     *
     * @param int $campaignid Campaign ID
     * @return object Statistics
     */
    public function get_campaign_statistics($campaignid) {
        global $DB;

        $stats = new \stdClass();
        $stats->campaign_id = $campaignid;

        if (!$DB->get_manager()->table_exists('local_sm_campaign_tracking')) {
            return $stats;
        }

        // Overall stats.
        $overall = $DB->get_record_sql("
            SELECT
                COUNT(*) as total_sent,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            FROM {local_sm_campaign_tracking}
            WHERE campaign_id = :campaignid AND status = 'sent'
        ", ['campaignid' => $campaignid]);

        $stats->total_sent = $overall->total_sent ?? 0;
        $stats->opened = $overall->opened ?? 0;
        $stats->clicked = $overall->clicked ?? 0;
        $stats->open_rate = $stats->total_sent > 0 ? round(($stats->opened / $stats->total_sent) * 100, 1) : 0;
        $stats->click_rate = $stats->total_sent > 0 ? round(($stats->clicked / $stats->total_sent) * 100, 1) : 0;

        // Stats by variant.
        $variants = $DB->get_records_sql("
            SELECT
                variant,
                COUNT(*) as sent,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            FROM {local_sm_campaign_tracking}
            WHERE campaign_id = :campaignid AND status = 'sent'
            GROUP BY variant
        ", ['campaignid' => $campaignid]);

        $stats->variants = [];
        foreach ($variants as $variant) {
            $variantstats = new \stdClass();
            $variantstats->variant = $variant->variant;
            $variantstats->sent = $variant->sent;
            $variantstats->opened = $variant->opened;
            $variantstats->clicked = $variant->clicked;
            $variantstats->open_rate = $variant->sent > 0 ? round(($variant->opened / $variant->sent) * 100, 1) : 0;
            $variantstats->click_rate = $variant->sent > 0 ? round(($variant->clicked / $variant->sent) * 100, 1) : 0;
            $stats->variants[] = $variantstats;
        }

        return $stats;
    }

    /**
     * Log campaign send.
     *
     * @param int $campaignid Campaign ID
     * @param object $stats Send statistics
     */
    protected function log_campaign_send($campaignid, $stats) {
        global $DB;

        $log = new \stdClass();
        $log->userid = 0;
        $log->action = 'campaign_sent';
        $log->details = json_encode([
            'campaign_id' => $campaignid,
            'total_recipients' => $stats->total_recipients,
            'sent' => $stats->sent,
            'failed' => $stats->failed,
            'duration' => $stats->duration
        ]);
        $log->timecreated = time();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Get all campaigns.
     *
     * @param string $status Filter by status
     * @return array Campaign records
     */
    public function get_campaigns($status = null) {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_sm_campaigns')) {
            return [];
        }

        if ($status) {
            return $DB->get_records('local_sm_campaigns', ['status' => $status], 'timecreated DESC');
        } else {
            return $DB->get_records('local_sm_campaigns', null, 'timecreated DESC');
        }
    }

    /**
     * Delete a campaign.
     *
     * @param int $campaignid Campaign ID
     * @return bool Success
     */
    public function delete_campaign($campaignid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_campaigns')) {
            // Delete tracking data.
            if ($DB->get_manager()->table_exists('local_sm_campaign_tracking')) {
                $DB->delete_records('local_sm_campaign_tracking', ['campaign_id' => $campaignid]);
            }

            // Delete variants.
            if ($DB->get_manager()->table_exists('local_sm_ab_variants')) {
                $DB->delete_records('local_sm_ab_variants', ['campaign_id' => $campaignid]);
            }

            // Delete campaign.
            return $DB->delete_records('local_sm_campaigns', ['id' => $campaignid]);
        }

        return false;
    }
}
