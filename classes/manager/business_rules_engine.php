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
 * Business rules engine for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Business rules engine class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class business_rules_engine {

    /**
     * Evaluate rules for a student.
     *
     * @param int $userid Student user ID
     * @param object $tracking Student tracking data
     * @return array Actions to execute
     */
    public function evaluate_rules($userid, $tracking) {
        $actions = [];

        // Get all active rules.
        $rules = $this->get_active_rules();

        foreach ($rules as $rule) {
            if ($this->evaluate_condition($rule, $tracking)) {
                $actions = array_merge($actions, $this->get_rule_actions($rule));

                // Log rule execution.
                $this->log_rule_execution($userid, $rule->id, $rule->rule_name);
            }
        }

        return $actions;
    }

    /**
     * Get all active rules.
     *
     * @return array Rule records
     */
    protected function get_active_rules() {
        global $DB;

        // Try custom rules table.
        if ($DB->get_manager()->table_exists('local_sm_business_rules')) {
            return $DB->get_records('local_sm_business_rules', ['enabled' => 1], 'priority ASC');
        }

        // Return default rules.
        return $this->get_default_rules();
    }

    /**
     * Get default business rules.
     *
     * @return array Default rules
     */
    protected function get_default_rules() {
        return [
            (object)[
                'id' => 1,
                'rule_name' => 'Critical inactivity auto-assign',
                'conditions' => json_encode([
                    'inactivity_days' => ['operator' => '>=', 'value' => 14],
                    'risk_level' => ['operator' => '==', 'value' => 'CRITICAL'],
                    'assigned_to' => ['operator' => '==', 'value' => null]
                ]),
                'actions' => json_encode([
                    'assign_supervisor' => true,
                    'create_task' => 'urgent_intervention',
                    'send_notification' => ['email', 'moodle', 'sms']
                ]),
                'priority' => 1,
                'enabled' => 1
            ],
            (object)[
                'id' => 2,
                'rule_name' => 'High risk follow-up',
                'conditions' => json_encode([
                    'inactivity_days' => ['operator' => '>=', 'value' => 7],
                    'risk_level' => ['operator' => '==', 'value' => 'HIGH'],
                    'notification_count' => ['operator' => '<', 'value' => 3]
                ]),
                'actions' => json_encode([
                    'send_notification' => ['email', 'moodle'],
                    'create_task' => 'follow_up'
                ]),
                'priority' => 2,
                'enabled' => 1
            ],
            (object)[
                'id' => 3,
                'rule_name' => 'Escalate after 48h no response',
                'conditions' => json_encode([
                    'risk_level' => ['operator' => '==', 'value' => 'CRITICAL'],
                    'hours_since_intervention' => ['operator' => '>=', 'value' => 48],
                    'response_received' => ['operator' => '==', 'value' => false]
                ]),
                'actions' => json_encode([
                    'escalate_to_coordinator' => true,
                    'increase_priority' => true
                ]),
                'priority' => 1,
                'enabled' => 1
            ],
            (object)[
                'id' => 4,
                'rule_name' => 'Missing assignments alert',
                'conditions' => json_encode([
                    'missing_activities' => ['operator' => '>=', 'value' => 3],
                    'notification_count' => ['operator' => '<', 'value' => 2]
                ]),
                'actions' => json_encode([
                    'send_notification' => ['email', 'moodle'],
                    'notify_supervisor' => true
                ]),
                'priority' => 3,
                'enabled' => 1
            ],
            (object)[
                'id' => 5,
                'rule_name' => 'Budget limit warning',
                'conditions' => json_encode([
                    'sms_budget_usage' => ['operator' => '>=', 'value' => 90]
                ]),
                'actions' => json_encode([
                    'disable_sms' => true,
                    'notify_admin' => true
                ]),
                'priority' => 1,
                'enabled' => 1
            ]
        ];
    }

    /**
     * Evaluate a rule condition.
     *
     * @param object $rule Rule record
     * @param object $tracking Student tracking data
     * @return bool True if condition met
     */
    protected function evaluate_condition($rule, $tracking) {
        $conditions = json_decode($rule->conditions, true);
        if (!$conditions) {
            return false;
        }

        foreach ($conditions as $field => $condition) {
            $value = $this->get_field_value($field, $tracking);
            $operator = $condition['operator'];
            $expected = $condition['value'];

            if (!$this->compare_values($value, $operator, $expected)) {
                return false; // All conditions must be true.
            }
        }

        return true;
    }

    /**
     * Get field value from tracking data.
     *
     * @param string $field Field name
     * @param object $tracking Tracking data
     * @return mixed Field value
     */
    protected function get_field_value($field, $tracking) {
        // Direct tracking fields.
        if (isset($tracking->$field)) {
            return $tracking->$field;
        }

        // Calculated fields.
        switch ($field) {
            case 'hours_since_intervention':
                return isset($tracking->last_intervention)
                    ? round((time() - $tracking->last_intervention) / 3600, 1)
                    : 999;

            case 'response_received':
                return isset($tracking->last_response) && $tracking->last_response > $tracking->last_intervention;

            case 'sms_budget_usage':
                $smstracker = new sms_cost_tracker();
                $monthlybudget = (float)get_config('local_student_monitor', 'sms_monthly_budget');
                if ($monthlybudget > 0) {
                    $monthlytotal = $smstracker->get_monthly_total();
                    return round(($monthlytotal / $monthlybudget) * 100, 1);
                }
                return 0;

            default:
                return null;
        }
    }

    /**
     * Compare values using operator.
     *
     * @param mixed $value Actual value
     * @param string $operator Comparison operator
     * @param mixed $expected Expected value
     * @return bool Comparison result
     */
    protected function compare_values($value, $operator, $expected) {
        switch ($operator) {
            case '==':
                return $value == $expected;
            case '!=':
                return $value != $expected;
            case '>':
                return $value > $expected;
            case '>=':
                return $value >= $expected;
            case '<':
                return $value < $expected;
            case '<=':
                return $value <= $expected;
            case 'in':
                return is_array($expected) && in_array($value, $expected);
            case 'not_in':
                return is_array($expected) && !in_array($value, $expected);
            default:
                return false;
        }
    }

    /**
     * Get actions from a rule.
     *
     * @param object $rule Rule record
     * @return array Actions
     */
    protected function get_rule_actions($rule) {
        $actions = json_decode($rule->actions, true);
        if (!$actions) {
            return [];
        }

        return $actions;
    }

    /**
     * Execute rule actions.
     *
     * @param int $userid Student user ID
     * @param array $actions Actions to execute
     * @param object $tracking Student tracking data
     * @return array Executed actions
     */
    public function execute_actions($userid, $actions, $tracking) {
        global $DB;

        $executed = [];

        foreach ($actions as $action => $params) {
            switch ($action) {
                case 'assign_supervisor':
                    if ($params && empty($tracking->assigned_to)) {
                        $workflowmanager = new workflow_manager();
                        $supervisor = $workflowmanager->get_default_supervisor();
                        if ($supervisor) {
                            $tracker = new student_tracker();
                            $tracker->assign_to_supervisor($userid, $supervisor->id);
                            $executed[] = 'assigned_supervisor';
                        }
                    }
                    break;

                case 'create_task':
                    if ($params && !empty($tracking->assigned_to)) {
                        $workflowmanager = new workflow_manager();
                        $workflowmanager->create_supervisor_task(
                            $tracking->assigned_to,
                            $userid,
                            $params,
                            [
                                'risk_level' => $tracking->risk_level,
                                'inactivity_days' => $tracking->inactivity_days
                            ]
                        );
                        $executed[] = 'task_created';
                    }
                    break;

                case 'send_notification':
                    if (is_array($params)) {
                        $notificationmanager = new notification_manager();
                        $user = $DB->get_record('user', ['id' => $userid]);

                        $subject = get_string('automatednotification', 'local_student_monitor');
                        $message = get_string('risknotificationmessage', 'local_student_monitor', [
                            'risklevel' => $tracking->risk_level,
                            'inactivity' => $tracking->inactivity_days
                        ]);

                        $notificationmanager->create_notification(
                            $userid,
                            'automated_rule',
                            $subject,
                            $message,
                            0,
                            $params
                        );
                        $executed[] = 'notification_sent';
                    }
                    break;

                case 'escalate_to_coordinator':
                    if ($params) {
                        $workflowmanager = new workflow_manager();
                        $workflowmanager->escalate_to_coordinator($userid, $tracking);
                        $executed[] = 'escalated';
                    }
                    break;

                case 'notify_supervisor':
                    if ($params && !empty($tracking->assigned_to)) {
                        $notificationmanager = new notification_manager();
                        $subject = get_string('supervisornotification', 'local_student_monitor');
                        $message = get_string('studentneedsattention', 'local_student_monitor', [
                            'studentname' => $DB->get_field('user', 'CONCAT(firstname, " ", lastname)', ['id' => $userid]),
                            'risklevel' => $tracking->risk_level
                        ]);

                        $notificationmanager->create_notification(
                            $tracking->assigned_to,
                            'supervisor_alert',
                            $subject,
                            $message,
                            0,
                            ['email', 'moodle']
                        );
                        $executed[] = 'supervisor_notified';
                    }
                    break;

                case 'disable_sms':
                    if ($params) {
                        set_config('sms_enabled', 0, 'local_student_monitor');
                        $executed[] = 'sms_disabled';
                    }
                    break;

                case 'notify_admin':
                    if ($params) {
                        $this->notify_system_admin('Budget limit reached');
                        $executed[] = 'admin_notified';
                    }
                    break;
            }
        }

        return $executed;
    }

    /**
     * Log rule execution.
     *
     * @param int $userid Student user ID
     * @param int $ruleid Rule ID
     * @param string $rulename Rule name
     */
    protected function log_rule_execution($userid, $ruleid, $rulename) {
        global $DB;

        $log = new \stdClass();
        $log->userid = $userid;
        $log->action = 'business_rule_executed';
        $log->details = json_encode([
            'rule_id' => $ruleid,
            'rule_name' => $rulename
        ]);
        $log->timecreated = time();

        $DB->insert_record('local_sm_logs', $log);
    }

    /**
     * Notify system administrator.
     *
     * @param string $message Alert message
     */
    protected function notify_system_admin($message) {
        $admins = get_admins();
        foreach ($admins as $admin) {
            $subject = get_string('systemalert', 'local_student_monitor');
            email_to_user($admin, \core_user::get_noreply_user(), $subject, $message);
        }
    }

    /**
     * Create a custom rule.
     *
     * @param string $rulename Rule name
     * @param array $conditions Rule conditions
     * @param array $actions Rule actions
     * @param int $priority Priority (1 = highest)
     * @return int Rule ID
     */
    public function create_rule($rulename, $conditions, $actions, $priority = 5) {
        global $DB;

        $rule = new \stdClass();
        $rule->rule_name = $rulename;
        $rule->conditions = json_encode($conditions);
        $rule->actions = json_encode($actions);
        $rule->priority = $priority;
        $rule->enabled = 1;
        $rule->timecreated = time();
        $rule->timemodified = time();

        if ($DB->get_manager()->table_exists('local_sm_business_rules')) {
            return $DB->insert_record('local_sm_business_rules', $rule);
        } else {
            // Fallback to config table.
            $config = new \stdClass();
            $config->courseid = 0;
            $config->config_type = 'business_rule';
            $config->config_key = 'rule_' . time();
            $config->config_value = json_encode($rule);
            $config->timecreated = time();

            return $DB->insert_record('local_sm_config', $config);
        }
    }

    /**
     * Test a rule against current data.
     *
     * @param int $ruleid Rule ID
     * @return array Test results
     */
    public function test_rule($ruleid) {
        global $DB;

        $rule = $DB->get_record('local_sm_business_rules', ['id' => $ruleid]);
        if (!$rule) {
            return ['error' => 'Rule not found'];
        }

        // Get sample students at risk.
        $students = $DB->get_records('local_sm_student_tracking', null, '', '*', 0, 10);

        $results = [
            'rule_name' => $rule->rule_name,
            'matches' => [],
            'no_matches' => []
        ];

        foreach ($students as $tracking) {
            if ($this->evaluate_condition($rule, $tracking)) {
                $results['matches'][] = $tracking->userid;
            } else {
                $results['no_matches'][] = $tracking->userid;
            }
        }

        $results['match_count'] = count($results['matches']);
        $results['total_tested'] = count($students);
        $results['match_percentage'] = count($students) > 0
            ? round((count($results['matches']) / count($students)) * 100, 1)
            : 0;

        return $results;
    }
}
