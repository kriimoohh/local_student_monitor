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
 * Student tracker manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Universite Numerique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

use local_student_monitor\risk_level;

defined('MOODLE_INTERNAL') || die();

/**
 * Class student_tracker
 *
 * Manages student tracking and risk assessment.
 */
class student_tracker {

    /** @var int Maximum days to look back in logs for activity */
    const LOG_LOOKBACK_DAYS = 90;

    /**
     * Update tracking data for a student.
     *
     * @param int $userid User ID
     * @param int|null $courseid Course ID (null for global tracking)
     * @return bool Success
     */
    public function update_student_tracking($userid, $courseid = null) {
        global $DB;

        // Get or create tracking record.
        $tracking = $this->get_or_create_tracking($userid, $courseid);

        // Update last activity.
        $tracking->last_activity = $this->get_last_activity($userid, $courseid);

        // Calculate inactivity days.
        $tracking->inactivity_days = $this->calculate_inactivity_days($tracking->last_activity);

        // Count missing assignments.
        if ($courseid) {
            $tracking->missing_assignments = $this->count_missing_assignments($userid, $courseid);
        }

        // Count notifications sent.
        $tracking->notification_count = $this->count_notifications($userid);

        // Calculate risk level using configured thresholds.
        $tracking->risk_level = $this->calculate_risk_level($tracking);

        // Determine if intervention is needed.
        $tracking->intervention_needed = risk_level::needs_intervention(
            $tracking->risk_level,
            $tracking->inactivity_days,
            $tracking->missing_assignments ?? 0
        ) ? 1 : 0;

        // Update timestamp.
        $tracking->timeupdated = time();

        // Save to database.
        return $DB->update_record('local_sm_student_tracking', $tracking);
    }

    /**
     * Get or create tracking record.
     *
     * @param int $userid User ID
     * @param int|null $courseid Course ID
     * @return \stdClass Tracking record
     */
    protected function get_or_create_tracking($userid, $courseid = null) {
        global $DB;

        $params = ['userid' => $userid];
        if ($courseid !== null) {
            $params['courseid'] = $courseid;
        } else {
            $params['courseid'] = null;
        }

        $tracking = $DB->get_record('local_sm_student_tracking', $params);

        if (!$tracking) {
            $tracking = new \stdClass();
            $tracking->userid = $userid;
            $tracking->courseid = $courseid;
            $tracking->risk_level = risk_level::LOW;
            $tracking->last_activity = time();
            $tracking->inactivity_days = 0;
            $tracking->missing_assignments = 0;
            $tracking->notification_count = 0;
            $tracking->intervention_needed = 0;
            $tracking->timeupdated = time();

            $tracking->id = $DB->insert_record('local_sm_student_tracking', $tracking);
        }

        return $tracking;
    }

    /**
     * Get last activity timestamp for a user.
     *
     * @param int $userid User ID
     * @param int|null $courseid Course ID
     * @return int Timestamp
     */
    protected function get_last_activity($userid, $courseid = null) {
        global $DB;

        $user = $DB->get_record('user', ['id' => $userid], 'lastaccess', MUST_EXIST);

        // If checking specific course, look at course-specific activity.
        if ($courseid) {
            // Add time boundary to limit query scope (performance optimization).
            $timelimit = time() - (self::LOG_LOOKBACK_DAYS * risk_level::SECONDS_PER_DAY);

            $sql = "SELECT MAX(timecreated) as lastaccess
                      FROM {logstore_standard_log}
                     WHERE userid = :userid
                       AND courseid = :courseid
                       AND timecreated > :timelimit";

            $courseactivity = $DB->get_record_sql($sql, [
                'userid' => $userid,
                'courseid' => $courseid,
                'timelimit' => $timelimit,
            ]);

            if ($courseactivity && $courseactivity->lastaccess) {
                return max($user->lastaccess, $courseactivity->lastaccess);
            }
        }

        return $user->lastaccess ?: 0;
    }

    /**
     * Calculate days of inactivity.
     *
     * @param int $lastactivity Last activity timestamp
     * @return int Days of inactivity
     */
    protected function calculate_inactivity_days($lastactivity) {
        if (!$lastactivity) {
            return risk_level::NEVER_LOGGED_IN_DAYS;
        }

        $diff = time() - $lastactivity;
        return (int) floor($diff / risk_level::SECONDS_PER_DAY);
    }

    /**
     * Count missing assignments for a student in a course.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @return int Number of missing assignments
     */
    protected function count_missing_assignments($userid, $courseid) {
        global $DB;

        // Get all assignments in the course that are past due.
        $sql = "SELECT a.id
                  FROM {assign} a
                 WHERE a.course = :courseid
                   AND a.duedate > 0
                   AND a.duedate < :now
                   AND NOT EXISTS (
                       SELECT 1
                         FROM {assign_submission} asub
                        WHERE asub.assignment = a.id
                          AND asub.userid = :userid
                          AND asub.status = 'submitted'
                   )";

        $params = [
            'courseid' => $courseid,
            'now' => time(),
            'userid' => $userid,
        ];

        $missing = $DB->get_records_sql($sql, $params);

        return count($missing);
    }

    /**
     * Count notifications sent to a user.
     *
     * @param int $userid User ID
     * @return int Number of notifications
     */
    protected function count_notifications($userid) {
        global $DB;

        return $DB->count_records('local_sm_notifications', ['userid' => $userid]);
    }

    /**
     * Calculate risk level based on tracking data.
     * Uses configured thresholds from plugin settings.
     *
     * @param \stdClass $tracking Tracking record
     * @return string Risk level constant
     */
    public function calculate_risk_level($tracking) {
        $score = 0;

        // Get configured thresholds.
        $thresholds = risk_level::get_inactivity_thresholds();

        // Inactivity score (max 40 points).
        if ($tracking->inactivity_days >= $thresholds['level3']) {
            $score += 40;
        } else if ($tracking->inactivity_days >= $thresholds['level2']) {
            $score += 25;
        } else if ($tracking->inactivity_days >= $thresholds['level1']) {
            $score += 10;
        }

        // Missing assignments score (max 30 points).
        if ($tracking->missing_assignments >= 5) {
            $score += 30;
        } else if ($tracking->missing_assignments >= 3) {
            $score += 20;
        } else if ($tracking->missing_assignments >= 1) {
            $score += 10;
        }

        // Notification count (many notifications might indicate persistent issues).
        // Max 20 points.
        if ($tracking->notification_count >= 10) {
            $score += 20;
        } else if ($tracking->notification_count >= 5) {
            $score += 10;
        }

        // Determine risk level based on score.
        return risk_level::from_score($score);
    }

    /**
     * Determine if intervention is needed.
     *
     * @param \stdClass $tracking Tracking record
     * @return int 1 if intervention needed, 0 otherwise
     * @deprecated Use risk_level::needs_intervention() instead
     */
    protected function needs_intervention($tracking) {
        return risk_level::needs_intervention(
            $tracking->risk_level,
            $tracking->inactivity_days,
            $tracking->missing_assignments ?? 0
        ) ? 1 : 0;
    }

    /**
     * Get students at risk.
     *
     * @param string|null $risklevel Filter by risk level (or minimum risk level with hierarchy)
     * @param int $limit Limit results
     * @param string|null $search Search by student name or email
     * @param bool $usehierarchy If true, filter includes all levels >= specified level
     * @return array Array of tracking records
     */
    public function get_students_at_risk($risklevel = null, $limit = 100, $search = null, $usehierarchy = true) {
        global $DB;

        $sql = "SELECT st.*, u.firstname, u.lastname, u.email
                  FROM {local_sm_student_tracking} st
                  JOIN {user} u ON u.id = st.userid
                 WHERE u.deleted = 0 AND u.suspended = 0";

        $params = [];

        if ($risklevel) {
            if ($usehierarchy) {
                // Use hierarchy to get all levels at or above the specified level.
                $filter = risk_level::get_sql_filter_at_least($risklevel);
                $sql .= " AND st.risk_level " . $filter['sql'];
                $params = array_merge($params, $filter['params']);
            } else {
                // Exact match only.
                $normalized = risk_level::normalize($risklevel);
                $legacy = risk_level::convert_to_legacy($risklevel);
                $sql .= " AND (st.risk_level = :risklevel OR st.risk_level = :risklevellegacy)";
                $params['risklevel'] = $normalized;
                $params['risklevellegacy'] = $legacy;
            }
        } else {
            // Only get students with at least MEDIUM risk.
            $filter = risk_level::get_sql_filter_at_least(risk_level::MEDIUM);
            $sql .= " AND st.risk_level " . $filter['sql'];
            $params = array_merge($params, $filter['params']);
        }

        if ($search) {
            $sql .= " AND (" . $DB->sql_like('u.firstname', ':search1', false) .
                    " OR " . $DB->sql_like('u.lastname', ':search2', false) .
                    " OR " . $DB->sql_like('u.email', ':search3', false) .
                    " OR " . $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname'), ':search4', false) . ")";
            $searchparam = '%' . $DB->sql_like_escape($search) . '%';
            $params['search1'] = $searchparam;
            $params['search2'] = $searchparam;
            $params['search3'] = $searchparam;
            $params['search4'] = $searchparam;
        }

        $sql .= " ORDER BY " . risk_level::get_sql_order_by('st.risk_level') . ", st.inactivity_days DESC";

        return $DB->get_records_sql($sql, $params, 0, $limit);
    }

    /**
     * Assign a student to a supervisor.
     *
     * @param int $studentid Student user ID
     * @param int $supervisorid Supervisor user ID
     * @param int|null $courseid Course ID
     * @return bool Success
     */
    public function assign_to_supervisor($studentid, $supervisorid, $courseid = null) {
        global $DB;

        $tracking = $this->get_or_create_tracking($studentid, $courseid);
        $tracking->assigned_to = $supervisorid;
        $tracking->timeupdated = time();

        return $DB->update_record('local_sm_student_tracking', $tracking);
    }

    /**
     * Add notes to a student's tracking record.
     *
     * @param int $studentid Student user ID
     * @param string $notes Notes to add
     * @param int|null $courseid Course ID
     * @return bool Success
     */
    public function add_notes($studentid, $notes, $courseid = null) {
        global $DB;

        $tracking = $this->get_or_create_tracking($studentid, $courseid);

        // Append notes with timestamp.
        $newnote = userdate(time()) . ': ' . $notes . "\n\n";
        $tracking->notes = $newnote . ($tracking->notes ?? '');
        $tracking->timeupdated = time();

        return $DB->update_record('local_sm_student_tracking', $tracking);
    }

    /**
     * Get tracking statistics.
     *
     * @return \stdClass Statistics object
     */
    public function get_statistics() {
        global $DB;

        $stats = new \stdClass();

        // Total students tracked.
        $stats->total_students = $DB->count_records('local_sm_student_tracking');

        // Students by risk level (support both new and legacy values).
        $stats->critical = $this->count_by_risk_level(risk_level::CRITICAL);
        $stats->high = $this->count_by_risk_level(risk_level::HIGH);
        $stats->medium = $this->count_by_risk_level(risk_level::MEDIUM);
        $stats->low = $this->count_by_risk_level(risk_level::LOW);

        // Legacy names for backward compatibility.
        $stats->critique = $stats->critical;
        $stats->eleve = $stats->high;
        $stats->moyen = $stats->medium;
        $stats->faible = $stats->low;

        // Students needing intervention.
        $stats->intervention_needed = $DB->count_records('local_sm_student_tracking', ['intervention_needed' => 1]);

        // Average inactivity days.
        $sql = "SELECT AVG(inactivity_days) as avg_inactivity
                  FROM {local_sm_student_tracking}";
        $result = $DB->get_record_sql($sql);
        $stats->avg_inactivity = round($result->avg_inactivity ?? 0, 1);

        return $stats;
    }

    /**
     * Count students by risk level (includes both new and legacy values).
     *
     * @param string $level Risk level constant
     * @return int Count
     */
    protected function count_by_risk_level($level) {
        global $DB;

        $normalized = risk_level::normalize($level);
        $legacy = risk_level::convert_to_legacy($level);

        $sql = "SELECT COUNT(*) FROM {local_sm_student_tracking}
                 WHERE risk_level = :level OR risk_level = :legacy";

        return (int) $DB->count_records_sql($sql, [
            'level' => $normalized,
            'legacy' => $legacy,
        ]);
    }
}
