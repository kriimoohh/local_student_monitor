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
 * Intervention tracking manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Intervention tracker class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intervention_tracker {

    /**
     * Log an intervention action.
     *
     * @param int $userid Student user ID
     * @param int $supervisorid Supervisor user ID
     * @param string $interventiontype Type of intervention
     * @param string $notes Intervention notes
     * @param array $metadata Additional metadata
     * @return int Intervention ID
     */
    public function log_intervention($userid, $supervisorid, $interventiontype, $notes, $metadata = []) {
        global $DB;

        $intervention = new \stdClass();
        $intervention->student_id = $userid;
        $intervention->supervisor_id = $supervisorid;
        $intervention->intervention_type = $interventiontype;
        $intervention->notes = $notes;
        $intervention->metadata = json_encode($metadata);
        $intervention->timecreated = time();

        // Check if interventions table exists.
        if ($DB->get_manager()->table_exists('local_sm_interventions')) {
            $interventionid = $DB->insert_record('local_sm_interventions', $intervention);
        } else {
            // Fallback to logs table.
            $log = new \stdClass();
            $log->userid = $supervisorid;
            $log->action = 'intervention_logged';
            $log->details = json_encode([
                'student_id' => $userid,
                'type' => $interventiontype,
                'notes' => $notes,
                'metadata' => $metadata
            ]);
            $log->timecreated = time();
            $interventionid = $DB->insert_record('local_sm_logs', $log);
        }

        // Update student tracking.
        $this->update_student_tracking($userid, $interventiontype);

        return $interventionid;
    }

    /**
     * Complete a task.
     *
     * @param int $taskid Task ID
     * @param int $supervisorid Supervisor ID
     * @param string $notes Completion notes
     * @return bool Success
     */
    public function complete_task($taskid, $supervisorid, $notes = '') {
        global $DB;

        $task = $DB->get_record('local_sm_tasks', ['id' => $taskid]);
        if (!$task) {
            return false;
        }

        // Update task status.
        $task->status = 'completed';
        $task->completed_by = $supervisorid;
        $task->completed_at = time();
        $task->completion_notes = $notes;
        $task->timemodified = time();

        $DB->update_record('local_sm_tasks', $task);

        // Log intervention.
        $this->log_intervention(
            $task->student_id,
            $supervisorid,
            'task_completed',
            $notes,
            [
                'task_id' => $taskid,
                'task_type' => $task->task_type,
                'priority' => $task->priority
            ]
        );

        return true;
    }

    /**
     * Defer a task to a new due date.
     *
     * @param int $taskid Task ID
     * @param int $newduedate New due date timestamp
     * @param string $reason Reason for deferment
     * @return bool Success
     */
    public function defer_task($taskid, $newduedate, $reason = '') {
        global $DB;

        $task = $DB->get_record('local_sm_tasks', ['id' => $taskid]);
        if (!$task) {
            return false;
        }

        $oldduedate = $task->due_date;
        $task->due_date = $newduedate;
        $task->timemodified = time();

        $DB->update_record('local_sm_tasks', $task);

        // Log the deferment.
        $log = new \stdClass();
        $log->userid = $task->supervisor_id;
        $log->action = 'task_deferred';
        $log->details = json_encode([
            'task_id' => $taskid,
            'old_due_date' => $oldduedate,
            'new_due_date' => $newduedate,
            'reason' => $reason
        ]);
        $log->timecreated = time();
        $DB->insert_record('local_sm_logs', $log);

        return true;
    }

    /**
     * Reassign a task to another supervisor.
     *
     * @param int $taskid Task ID
     * @param int $newsupervisorid New supervisor ID
     * @return bool Success
     */
    public function reassign_task($taskid, $newsupervisorid) {
        global $DB;

        $task = $DB->get_record('local_sm_tasks', ['id' => $taskid]);
        if (!$task) {
            return false;
        }

        $oldsupervisor = $task->supervisor_id;
        $task->supervisor_id = $newsupervisorid;
        $task->timemodified = time();

        $DB->update_record('local_sm_tasks', $task);

        // Log the reassignment.
        $log = new \stdClass();
        $log->userid = $oldsupervisor;
        $log->action = 'task_reassigned';
        $log->details = json_encode([
            'task_id' => $taskid,
            'old_supervisor' => $oldsupervisor,
            'new_supervisor' => $newsupervisorid,
            'student_id' => $task->student_id
        ]);
        $log->timecreated = time();
        $DB->insert_record('local_sm_logs', $log);

        // Notify new supervisor.
        $notificationmanager = new notification_manager();
        $user = $DB->get_record('user', ['id' => $newsupervisorid]);

        $subject = get_string('taskreassignedsubject', 'local_student_monitor');
        $message = get_string('taskreassignedmessage', 'local_student_monitor', [
            'tasktype' => $task->task_type,
            'duedate' => userdate($task->due_date)
        ]);

        $notificationmanager->create_notification(
            $newsupervisorid,
            'task_assigned',
            $subject,
            $message,
            0,
            ['email', 'moodle']
        );

        return true;
    }

    /**
     * Update student tracking after intervention.
     *
     * @param int $userid Student user ID
     * @param string $interventiontype Intervention type
     */
    protected function update_student_tracking($userid, $interventiontype) {
        global $DB;

        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);
        if (!$tracking) {
            return;
        }

        // Reset notification count if intervention was effective.
        if (in_array($interventiontype, ['phone_call', 'meeting', 'email_response'])) {
            $tracking->notification_count = 0;
        }

        $tracking->timeupdated = time();
        $DB->update_record('local_sm_student_tracking', $tracking);
    }

    /**
     * Get intervention history for a student.
     *
     * @param int $userid Student user ID
     * @param int $limit Number of records to return
     * @return array Intervention records
     */
    public function get_intervention_history($userid, $limit = 50) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_interventions')) {
            return $DB->get_records_sql("
                SELECT i.*,
                       u.firstname,
                       u.lastname
                FROM {local_sm_interventions} i
                LEFT JOIN {user} u ON u.id = i.supervisor_id
                WHERE i.student_id = :userid
                ORDER BY i.timecreated DESC
            ", ['userid' => $userid], 0, $limit);
        } else {
            // Fallback to logs table.
            $logs = $DB->get_records_sql("
                SELECT *
                FROM {local_sm_logs}
                WHERE action = 'intervention_logged'
                  AND details LIKE :userid
                ORDER BY timecreated DESC
            ", ['userid' => '%"student_id":' . $userid . '%'], 0, $limit);

            $interventions = [];
            foreach ($logs as $log) {
                $details = json_decode($log->details);
                if ($details && $details->student_id == $userid) {
                    $intervention = new \stdClass();
                    $intervention->id = $log->id;
                    $intervention->student_id = $details->student_id;
                    $intervention->supervisor_id = $log->userid;
                    $intervention->intervention_type = $details->type;
                    $intervention->notes = $details->notes;
                    $intervention->timecreated = $log->timecreated;
                    $interventions[] = $intervention;
                }
            }

            return $interventions;
        }
    }

    /**
     * Get intervention statistics for a supervisor.
     *
     * @param int $supervisorid Supervisor user ID
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return object Statistics
     */
    public function get_supervisor_statistics($supervisorid, $startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = strtotime('first day of this month');
        }
        if (!$enddate) {
            $enddate = time();
        }

        $stats = new \stdClass();

        // Count interventions by type.
        if ($DB->get_manager()->table_exists('local_sm_interventions')) {
            $sql = "SELECT
                        intervention_type,
                        COUNT(*) as count
                    FROM {local_sm_interventions}
                    WHERE supervisor_id = :supervisorid
                      AND timecreated >= :startdate
                      AND timecreated <= :enddate
                    GROUP BY intervention_type";

            $stats->by_type = $DB->get_records_sql($sql, [
                'supervisorid' => $supervisorid,
                'startdate' => $startdate,
                'enddate' => $enddate
            ]);
        } else {
            $stats->by_type = [];
        }

        // Count tasks.
        if ($DB->get_manager()->table_exists('local_sm_tasks')) {
            $stats->tasks_completed = $DB->count_records_sql("
                SELECT COUNT(*)
                FROM {local_sm_tasks}
                WHERE completed_by = :supervisorid
                  AND completed_at >= :startdate
                  AND completed_at <= :enddate
            ", ['supervisorid' => $supervisorid, 'startdate' => $startdate, 'enddate' => $enddate]);

            $stats->tasks_pending = $DB->count_records('local_sm_tasks', [
                'supervisor_id' => $supervisorid,
                'status' => 'pending'
            ]);

            $stats->tasks_overdue = $DB->count_records_sql("
                SELECT COUNT(*)
                FROM {local_sm_tasks}
                WHERE supervisor_id = :supervisorid
                  AND status != 'completed'
                  AND due_date < :now
            ", ['supervisorid' => $supervisorid, 'now' => time()]);
        } else {
            $stats->tasks_completed = 0;
            $stats->tasks_pending = 0;
            $stats->tasks_overdue = 0;
        }

        // Calculate response time.
        $stats->avg_response_time = $this->calculate_average_response_time($supervisorid, $startdate, $enddate);

        return $stats;
    }

    /**
     * Calculate average response time for a supervisor.
     *
     * @param int $supervisorid Supervisor user ID
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return int Average response time in seconds
     */
    protected function calculate_average_response_time($supervisorid, $startdate, $enddate) {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_sm_tasks')) {
            return 0;
        }

        $result = $DB->get_record_sql("
            SELECT AVG(completed_at - timecreated) as avg_time
            FROM {local_sm_tasks}
            WHERE completed_by = :supervisorid
              AND status = 'completed'
              AND completed_at >= :startdate
              AND completed_at <= :enddate
        ", ['supervisorid' => $supervisorid, 'startdate' => $startdate, 'enddate' => $enddate]);

        return $result ? (int)$result->avg_time : 0;
    }

    /**
     * Get effectiveness metrics for interventions.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @return object Effectiveness metrics
     */
    public function get_effectiveness_metrics($startdate = null, $enddate = null) {
        global $DB;

        if (!$startdate) {
            $startdate = strtotime('first day of this month');
        }
        if (!$enddate) {
            $enddate = time();
        }

        $metrics = new \stdClass();

        // Students who improved after intervention (using timeupdated).
        $metrics->students_improved = $DB->count_records_sql("
            SELECT COUNT(DISTINCT st.userid)
            FROM {local_sm_student_tracking} st
            WHERE st.timeupdated >= :startdate
              AND st.timeupdated <= :enddate
              AND st.risk_level IN ('LOW', 'MEDIUM')
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        // Students who remained at risk.
        $metrics->students_at_risk = $DB->count_records_sql("
            SELECT COUNT(DISTINCT st.userid)
            FROM {local_sm_student_tracking} st
            WHERE st.timeupdated >= :startdate
              AND st.timeupdated <= :enddate
              AND st.risk_level IN ('HIGH', 'CRITICAL')
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        // Intervention success rate.
        $total = $metrics->students_improved + $metrics->students_at_risk;
        $metrics->success_rate = $total > 0 ? round(($metrics->students_improved / $total) * 100, 1) : 0;

        // Average notifications per student.
        $avgresult = $DB->get_record_sql("
            SELECT AVG(notification_count) as avg_count
            FROM {local_sm_student_tracking}
            WHERE timeupdated >= :startdate
              AND timeupdated <= :enddate
        ", ['startdate' => $startdate, 'enddate' => $enddate]);

        $metrics->avg_interventions_per_student = $avgresult ? round($avgresult->avg_count ?? 0, 1) : 0;

        return $metrics;
    }

    /**
     * Get escalation history for critical cases.
     *
     * @param int $limit Number of records
     * @return array Escalation records
     */
    public function get_escalation_history($limit = 20) {
        global $DB;

        return $DB->get_records_sql("
            SELECT l.*,
                   u.firstname,
                   u.lastname,
                   u.email
            FROM {local_sm_logs} l
            LEFT JOIN {user} u ON u.id = l.userid
            WHERE l.action = 'escalated_to_coordinator'
            ORDER BY l.timecreated DESC
        ", [], 0, $limit);
    }
}
