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
 * Workflow automation manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Workflow automation manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workflow_manager {

    /**
     * Execute workflows for a student based on their risk level.
     *
     * @param int $userid User ID
     * @param string $risklevel Risk level
     * @param object $tracking Tracking data
     * @return array Actions taken
     */
    public function execute_risk_workflows($userid, $risklevel, $tracking) {
        $actions = [];

        switch ($risklevel) {
            case 'CRITIQUE':
                $actions = array_merge($actions, $this->execute_critical_workflow($userid, $tracking));
                break;

            case 'ÉLEVÉ':
                $actions = array_merge($actions, $this->execute_high_workflow($userid, $tracking));
                break;

            case 'MOYEN':
                $actions = array_merge($actions, $this->execute_medium_workflow($userid, $tracking));
                break;

            case 'FAIBLE':
                // No automatic workflow for low risk.
                break;
        }

        return $actions;
    }

    /**
     * Execute critical risk workflow.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @return array Actions taken
     */
    protected function execute_critical_workflow($userid, $tracking) {
        global $DB;

        $actions = [];

        // Action 1: Auto-assign to default supervisor if not assigned.
        if (empty($tracking->assigned_to)) {
            $supervisor = $this->get_default_supervisor();
            if ($supervisor) {
                $tracker = new student_tracker();
                $tracker->assign_to_supervisor($userid, $supervisor->id);
                $actions[] = 'assigned_to_supervisor';

                // Create task for supervisor.
                $this->create_supervisor_task($supervisor->id, $userid, 'urgent_intervention', [
                    'risk_level' => 'CRITIQUE',
                    'inactivity_days' => $tracking->inactivity_days,
                    'missing_assignments' => $tracking->missing_assignments
                ]);
                $actions[] = 'task_created';
            }
        }

        // Action 2: Send urgent notification to student.
        if ($tracking->notification_count < 3) {
            $notificationmanager = new notification_manager();
            $user = $DB->get_record('user', ['id' => $userid]);

            $subject = get_string('urgentintervention', 'local_student_monitor');
            $message = get_string('criticalriskmessage', 'local_student_monitor');

            $notificationmanager->create_notification(
                $userid,
                'urgent_intervention',
                $subject,
                $message,
                0,
                ['email', 'moodle', 'sms']
            );
            $actions[] = 'urgent_notification_sent';
        }

        // Action 3: Escalate to academic coordinator if no response after 48h.
        $laststatus = $this->get_last_intervention_status($userid);
        if ($laststatus && (time() - $laststatus->timecreated) > (48 * 60 * 60)) {
            $this->escalate_to_coordinator($userid, $tracking);
            $actions[] = 'escalated_to_coordinator';
        }

        // Log workflow execution.
        $this->log_workflow_execution($userid, 'critical_workflow', $actions);

        return $actions;
    }

    /**
     * Execute high risk workflow.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @return array Actions taken
     */
    protected function execute_high_workflow($userid, $tracking) {
        global $DB;

        $actions = [];

        // Action 1: Auto-assign if not assigned and inactivity > 7 days.
        if (empty($tracking->assigned_to) && $tracking->inactivity_days > 7) {
            $supervisor = $this->get_default_supervisor();
            if ($supervisor) {
                $tracker = new student_tracker();
                $tracker->assign_to_supervisor($userid, $supervisor->id);
                $actions[] = 'assigned_to_supervisor';

                // Create task.
                $this->create_supervisor_task($supervisor->id, $userid, 'follow_up', [
                    'risk_level' => 'ÉLEVÉ',
                    'inactivity_days' => $tracking->inactivity_days
                ]);
                $actions[] = 'task_created';
            }
        }

        // Action 2: Send reminder if no contact in last 7 days.
        $lastcontact = $this->get_last_contact_date($userid);
        if (!$lastcontact || (time() - $lastcontact) > (7 * 24 * 60 * 60)) {
            $notificationmanager = new notification_manager();

            $subject = get_string('followupreminder', 'local_student_monitor');
            $message = get_string('highriskmessage', 'local_student_monitor');

            $notificationmanager->create_notification(
                $userid,
                'follow_up',
                $subject,
                $message,
                0,
                ['email', 'moodle']
            );
            $actions[] = 'reminder_sent';
        }

        $this->log_workflow_execution($userid, 'high_workflow', $actions);

        return $actions;
    }

    /**
     * Execute medium risk workflow.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @return array Actions taken
     */
    protected function execute_medium_workflow($userid, $tracking) {
        $actions = [];

        // Action: Send preventive reminder if inactivity > 5 days.
        if ($tracking->inactivity_days > 5 && $tracking->notification_count < 2) {
            $notificationmanager = new notification_manager();

            $subject = get_string('preventivereminder', 'local_student_monitor');
            $message = get_string('mediumriskmessage', 'local_student_monitor');

            $notificationmanager->create_notification(
                $userid,
                'preventive',
                $subject,
                $message,
                0,
                ['email', 'moodle']
            );
            $actions[] = 'preventive_reminder_sent';
        }

        $this->log_workflow_execution($userid, 'medium_workflow', $actions);

        return $actions;
    }

    /**
     * Create a task for a supervisor.
     *
     * @param int $supervisorid Supervisor user ID
     * @param int $studentid Student user ID
     * @param string $tasktype Task type
     * @param array $data Additional data
     * @return int Task ID
     */
    public function create_supervisor_task($supervisorid, $studentid, $tasktype, $data = []) {
        global $DB;

        $task = new \stdClass();
        $task->supervisor_id = $supervisorid;
        $task->student_id = $studentid;
        $task->task_type = $tasktype;
        $task->priority = $this->get_task_priority($tasktype);
        $task->status = 'pending';
        $task->data = json_encode($data);
        $task->due_date = $this->calculate_due_date($tasktype);
        $task->timecreated = time();
        $task->timemodified = time();

        // Check if table exists.
        if ($DB->get_manager()->table_exists('local_sm_tasks')) {
            return $DB->insert_record('local_sm_tasks', $task);
        } else {
            // Fallback: log as action.
            $log = new \stdClass();
            $log->userid = $supervisorid;
            $log->action = 'task_created';
            $log->details = json_encode([
                'student_id' => $studentid,
                'task_type' => $tasktype,
                'data' => $data
            ]);
            $log->timecreated = time();
            return $DB->insert_record('local_sm_logs', $log);
        }
    }

    /**
     * Get task priority based on type.
     *
     * @param string $tasktype Task type
     * @return string Priority (urgent, high, normal, low)
     */
    protected function get_task_priority($tasktype) {
        $priorities = [
            'urgent_intervention' => 'urgent',
            'follow_up' => 'high',
            'preventive' => 'normal',
            'check_in' => 'low'
        ];

        return $priorities[$tasktype] ?? 'normal';
    }

    /**
     * Calculate due date based on task type.
     *
     * @param string $tasktype Task type
     * @return int Timestamp
     */
    protected function calculate_due_date($tasktype) {
        $durations = [
            'urgent_intervention' => 24 * 60 * 60,  // 24 hours.
            'follow_up' => 3 * 24 * 60 * 60,       // 3 days.
            'preventive' => 7 * 24 * 60 * 60,      // 7 days.
            'check_in' => 14 * 24 * 60 * 60        // 14 days.
        ];

        $duration = $durations[$tasktype] ?? (7 * 24 * 60 * 60);
        return time() + $duration;
    }

    /**
     * Get default supervisor.
     *
     * @return object|false Supervisor user or false
     */
    protected function get_default_supervisor() {
        global $DB;

        // Get from settings or return first user with intervene capability.
        $supervisorid = get_config('local_student_monitor', 'default_supervisor_id');

        if ($supervisorid) {
            return $DB->get_record('user', ['id' => $supervisorid]);
        }

        // Fallback: get first user with intervene capability.
        $supervisors = get_users_by_capability(
            \context_system::instance(),
            'local/student_monitor:intervene',
            'u.*',
            '',
            0,
            1
        );

        return !empty($supervisors) ? reset($supervisors) : false;
    }

    /**
     * Get last intervention status for a student.
     *
     * @param int $userid User ID
     * @return object|false Status record or false
     */
    protected function get_last_intervention_status($userid) {
        global $DB;

        return $DB->get_record_sql("
            SELECT *
            FROM {local_sm_logs}
            WHERE action LIKE '%intervention%'
                AND details LIKE :userid
            ORDER BY timecreated DESC
            LIMIT 1
        ", ['userid' => '%"student_id":' . $userid . '%']);
    }

    /**
     * Get last contact date with student.
     *
     * @param int $userid User ID
     * @return int|false Timestamp or false
     */
    protected function get_last_contact_date($userid) {
        global $DB;

        $record = $DB->get_record_sql("
            SELECT MAX(timecreated) as lastcontact
            FROM {local_sm_notifications}
            WHERE userid = :userid
                AND status IN ('sent', 'delivered', 'read')
        ", ['userid' => $userid]);

        return $record ? $record->lastcontact : false;
    }

    /**
     * Escalate to academic coordinator.
     *
     * @param int $userid Student user ID
     * @param object $tracking Tracking data
     * @return bool Success
     */
    protected function escalate_to_coordinator($userid, $tracking) {
        global $DB;

        // Get coordinator email from settings.
        $coordinatoremail = get_config('local_student_monitor', 'coordinator_email');

        if (empty($coordinatoremail)) {
            return false;
        }

        // Send escalation email.
        $user = $DB->get_record('user', ['id' => $userid]);
        $subject = get_string('escalationsubject', 'local_student_monitor');
        $message = get_string('escalationmessage', 'local_student_monitor', [
            'studentname' => fullname($user),
            'risklevel' => $tracking->risk_level,
            'inactivity' => $tracking->inactivity_days,
            'missing' => $tracking->missing_assignments
        ]);

        // Create coordinator user object.
        $coordinator = new \stdClass();
        $coordinator->email = $coordinatoremail;
        $coordinator->firstname = 'Coordinator';
        $coordinator->lastname = 'Academic';
        $coordinator->maildisplay = 1;

        $from = \core_user::get_noreply_user();

        return email_to_user($coordinator, $from, $subject, $message);
    }

    /**
     * Log workflow execution.
     *
     * @param int $userid User ID
     * @param string $workflowtype Workflow type
     * @param array $actions Actions taken
     */
    protected function log_workflow_execution($userid, $workflowtype, $actions) {
        global $DB;

        $log = new \stdClass();
        $log->userid = $userid;
        $log->action = 'workflow_executed';
        $log->details = json_encode([
            'workflow_type' => $workflowtype,
            'actions' => $actions,
            'timestamp' => time()
        ]);
        $log->timecreated = time();

        $DB->insert_record('local_sm_logs', $log);
    }
}
