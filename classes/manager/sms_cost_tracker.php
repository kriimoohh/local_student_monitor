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
 * SMS cost tracking manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * SMS cost tracker class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sms_cost_tracker {

    /**
     * Track SMS cost.
     *
     * @param int $notificationid Notification ID
     * @param string $phone Phone number
     * @param string $message Message content
     * @param float $cost Cost in local currency
     * @return int Log ID
     */
    public function track_sms($notificationid, $phone, $message, $cost = 0) {
        global $DB;

        // Calculate message length and parts.
        $length = strlen($message);
        $parts = ceil($length / 160);

        // Get cost per SMS from settings.
        if ($cost == 0) {
            $cost = (float)get_config('local_student_monitor', 'sms_cost_per_message');
            if (!$cost) {
                $cost = 25; // Default cost in XOF (Senegal).
            }
        }

        $totalcost = $cost * $parts;

        // Create log entry.
        $log = new \stdClass();
        $log->notification_id = $notificationid;
        $log->phone_number = $phone;
        $log->message_length = $length;
        $log->message_parts = $parts;
        $log->cost_per_sms = $cost;
        $log->total_cost = $totalcost;
        $log->currency = get_config('local_student_monitor', 'sms_currency') ?: 'XOF';
        $log->timecreated = time();

        // Check if table exists, if not use logs table with specific action.
        if ($DB->get_manager()->table_exists('local_sm_sms_costs')) {
            return $DB->insert_record('local_sm_sms_costs', $log);
        } else {
            // Fallback to logs table.
            $logentry = new \stdClass();
            $logentry->userid = 0;
            $logentry->action = 'sms_sent';
            $logentry->details = json_encode([
                'notification_id' => $notificationid,
                'phone' => $phone,
                'parts' => $parts,
                'cost' => $totalcost,
                'currency' => $log->currency
            ]);
            $logentry->timecreated = time();
            return $DB->insert_record('local_sm_logs', $logentry);
        }
    }

    /**
     * Get SMS cost statistics for a period.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return object Statistics
     */
    public function get_cost_statistics($startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = strtotime('first day of this month', time());
        }
        if (!$enddate) {
            $enddate = time();
        }

        $stats = new \stdClass();

        // Try SMS costs table first.
        if ($DB->get_manager()->table_exists('local_sm_sms_costs')) {
            $sql = "SELECT
                        COUNT(*) as sms_count,
                        SUM(message_parts) as total_parts,
                        SUM(total_cost) as total_cost,
                        AVG(total_cost) as avg_cost,
                        currency
                    FROM {local_sm_sms_costs}
                    WHERE timecreated >= :startdate AND timecreated <= :enddate
                    GROUP BY currency";

            $result = $DB->get_record_sql($sql, ['startdate' => $startdate, 'enddate' => $enddate]);

            if ($result) {
                $stats->sms_count = $result->sms_count;
                $stats->total_parts = $result->total_parts;
                $stats->total_cost = $result->total_cost;
                $stats->avg_cost = round($result->avg_cost, 2);
                $stats->currency = $result->currency;
            } else {
                $stats->sms_count = 0;
                $stats->total_parts = 0;
                $stats->total_cost = 0;
                $stats->avg_cost = 0;
                $stats->currency = 'XOF';
            }
        } else {
            // Fallback to logs table.
            $logs = $DB->get_records_sql("
                SELECT *
                FROM {local_sm_logs}
                WHERE action = 'sms_sent'
                    AND timecreated >= :startdate
                    AND timecreated <= :enddate
            ", ['startdate' => $startdate, 'enddate' => $enddate]);

            $totalcost = 0;
            $totalparts = 0;
            $count = 0;
            $currency = 'XOF';

            foreach ($logs as $log) {
                $details = json_decode($log->details);
                if ($details) {
                    $totalcost += $details->cost;
                    $totalparts += $details->parts;
                    $count++;
                    if (isset($details->currency)) {
                        $currency = $details->currency;
                    }
                }
            }

            $stats->sms_count = $count;
            $stats->total_parts = $totalparts;
            $stats->total_cost = $totalcost;
            $stats->avg_cost = $count > 0 ? round($totalcost / $count, 2) : 0;
            $stats->currency = $currency;
        }

        return $stats;
    }

    /**
     * Get daily SMS costs for a period.
     *
     * @param int $days Number of days to look back
     * @return array Array of daily costs
     */
    public function get_daily_costs($days = 30) {
        global $DB;

        $startdate = time() - ($days * 24 * 60 * 60);
        $dailycosts = [];

        for ($i = 0; $i < $days; $i++) {
            $daystart = strtotime("midnight -$i days");
            $dayend = strtotime("midnight -" . ($i - 1) . " days");

            $daystats = $this->get_cost_statistics($daystart, $dayend);

            $dailycosts[] = [
                'date' => $daystart,
                'sms_count' => $daystats->sms_count,
                'total_cost' => $daystats->total_cost
            ];
        }

        return array_reverse($dailycosts);
    }

    /**
     * Get cost breakdown by notification type.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return array Breakdown by type
     */
    public function get_cost_by_type($startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = strtotime('first day of this month', time());
        }
        if (!$enddate) {
            $enddate = time();
        }

        $sql = "SELECT
                    n.type as notification_type,
                    COUNT(DISTINCT n.id) as notification_count,
                    SUM(CASE WHEN JSON_EXTRACT(n.channels, '$.sms') = 1 THEN 1 ELSE 0 END) as sms_count
                FROM {local_sm_notifications} n
                WHERE n.timecreated >= :startdate
                    AND n.timecreated <= :enddate
                    AND n.channels LIKE '%sms%'
                GROUP BY n.type
                ORDER BY notification_count DESC";

        try {
            $results = $DB->get_records_sql($sql, ['startdate' => $startdate, 'enddate' => $enddate]);
        } catch (\Exception $e) {
            // Fallback query for databases that don't support JSON_EXTRACT.
            $sql = "SELECT
                        n.type as notification_type,
                        COUNT(*) as notification_count
                    FROM {local_sm_notifications} n
                    WHERE n.timecreated >= :startdate
                        AND n.timecreated <= :enddate
                        AND n.channels LIKE '%sms%'
                    GROUP BY n.type
                    ORDER BY notification_count DESC";

            $results = $DB->get_records_sql($sql, ['startdate' => $startdate, 'enddate' => $enddate]);
        }

        return $results;
    }

    /**
     * Get total spent this month.
     *
     * @return float Total cost
     */
    public function get_monthly_total() {
        $startdate = strtotime('first day of this month', time());
        $stats = $this->get_cost_statistics($startdate);
        return $stats->total_cost;
    }

    /**
     * Check if budget limit is reached.
     *
     * @param float $budgetlimit Budget limit
     * @return bool True if limit reached
     */
    public function is_budget_limit_reached($budgetlimit = null) {
        if (!$budgetlimit) {
            $budgetlimit = (float)get_config('local_student_monitor', 'sms_monthly_budget');
        }

        if (!$budgetlimit) {
            return false; // No limit set.
        }

        $monthlytotal = $this->get_monthly_total();
        return $monthlytotal >= $budgetlimit;
    }
}
