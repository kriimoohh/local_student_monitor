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
 * Peer comparison manager for anonymous performance comparisons.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Peer comparison manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class peer_comparison {

    /**
     * Get peer comparison data for a student.
     *
     * @param int $userid User ID
     * @return object Comparison data
     */
    public function get_peer_comparison($userid) {
        global $DB;

        $comparison = new \stdClass();

        // Get user's courses.
        $usercourses = enrol_get_users_courses($userid, true);
        $courseids = array_keys($usercourses);

        if (empty($courseids)) {
            return $this->get_empty_comparison();
        }

        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // Get peer group (students in same courses).
        $comparison->peer_count = $this->get_peer_count($userid, $courseids);

        // Compare login frequency.
        $comparison->login_frequency = $this->compare_login_frequency($userid, $courseids);

        // Compare assignment completion.
        $comparison->assignment_completion = $this->compare_assignment_completion($userid, $courseids);

        // Compare engagement score.
        $comparison->engagement_score = $this->compare_engagement($userid, $courseids);

        // Compare grade performance.
        $comparison->grade_performance = $this->compare_grades($userid, $courseids);

        // Calculate overall percentile.
        $comparison->overall_percentile = $this->calculate_overall_percentile($comparison);

        // Get performance category.
        $comparison->category = $this->get_performance_category($comparison->overall_percentile);

        return $comparison;
    }

    /**
     * Get empty comparison object.
     *
     * @return object Empty comparison
     */
    protected function get_empty_comparison() {
        $comparison = new \stdClass();
        $comparison->peer_count = 0;
        $comparison->login_frequency = (object)['user_value' => 0, 'peer_avg' => 0, 'percentile' => 50];
        $comparison->assignment_completion = (object)['user_value' => 0, 'peer_avg' => 0, 'percentile' => 50];
        $comparison->engagement_score = (object)['user_value' => 0, 'peer_avg' => 0, 'percentile' => 50];
        $comparison->grade_performance = (object)['user_value' => 0, 'peer_avg' => 0, 'percentile' => 50];
        $comparison->overall_percentile = 50;
        $comparison->category = 'average';

        return $comparison;
    }

    /**
     * Get count of peers.
     *
     * @param int $userid User ID
     * @param array $courseids Course IDs
     * @return int Peer count
     */
    protected function get_peer_count($userid, $courseids) {
        global $DB;

        if (empty($courseids)) {
            return 0;
        }

        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $sql = "SELECT COUNT(DISTINCT ue.userid) as peer_count
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE e.courseid $coursesql
                AND ue.userid != :userid
                AND ue.status = 0";

        $params = array_merge($courseparams, ['userid' => $userid]);
        $result = $DB->get_record_sql($sql, $params);

        return $result->peer_count ?? 0;
    }

    /**
     * Compare login frequency with peers.
     *
     * @param int $userid User ID
     * @param array $courseids Course IDs
     * @return object Comparison data
     */
    protected function compare_login_frequency($userid, $courseids) {
        global $DB;

        $since = time() - (30 * 24 * 3600);

        // Get user's login count.
        $userlogins = $DB->count_records_select('logstore_standard_log',
            'userid = :userid AND action = :action AND timecreated > :since',
            ['userid' => $userid, 'action' => 'loggedin', 'since' => $since]
        );

        // Get peer average.
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $sql = "SELECT AVG(login_count) as avg_logins
                FROM (
                    SELECT l.userid, COUNT(*) as login_count
                    FROM {logstore_standard_log} l
                    JOIN {user_enrolments} ue ON ue.userid = l.userid
                    JOIN {enrol} e ON e.id = ue.enrolid
                    WHERE e.courseid $coursesql
                    AND l.userid != :userid
                    AND l.action = :action
                    AND l.timecreated > :since
                    GROUP BY l.userid
                ) subquery";

        $params = array_merge($courseparams, [
            'userid' => $userid,
            'action' => 'loggedin',
            'since' => $since
        ]);

        $result = $DB->get_record_sql($sql, $params);
        $peeravg = $result->avg_logins ?? 0;

        // Calculate percentile.
        $percentile = $this->calculate_percentile($userlogins, $peeravg, 20);

        return (object)[
            'user_value' => $userlogins,
            'peer_avg' => round($peeravg, 1),
            'percentile' => $percentile
        ];
    }

    /**
     * Compare assignment completion with peers.
     *
     * @param int $userid User ID
     * @param array $courseids Course IDs
     * @return object Comparison data
     */
    protected function compare_assignment_completion($userid, $courseids) {
        global $DB;

        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // Get user's completion rate.
        $sql = "SELECT
                    COUNT(DISTINCT a.id) as total_assignments,
                    COUNT(DISTINCT s.assignment) as completed_assignments
                FROM {assign} a
                JOIN {course_modules} cm ON cm.instance = a.id AND cm.module =
                    (SELECT id FROM {modules} WHERE name = 'assign')
                LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = :userid
                    AND s.status = 'submitted'
                WHERE a.course $coursesql";

        $params = array_merge($courseparams, ['userid' => $userid]);
        $userdata = $DB->get_record_sql($sql, $params);

        $userrate = $userdata->total_assignments > 0 ?
            ($userdata->completed_assignments / $userdata->total_assignments) * 100 : 0;

        // Get peer average.
        $sql = "SELECT AVG(completion_rate) as avg_rate
                FROM (
                    SELECT ue.userid,
                           COUNT(DISTINCT s.assignment) * 100.0 / COUNT(DISTINCT a.id) as completion_rate
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {assign} a ON a.course = e.courseid
                    JOIN {course_modules} cm ON cm.instance = a.id AND cm.module =
                        (SELECT id FROM {modules} WHERE name = 'assign')
                    LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = ue.userid
                        AND s.status = 'submitted'
                    WHERE e.courseid $coursesql
                    AND ue.userid != :userid
                    GROUP BY ue.userid
                    HAVING COUNT(DISTINCT a.id) > 0
                ) subquery";

        $result = $DB->get_record_sql($sql, $params);
        $peeravg = $result->avg_rate ?? 0;

        $percentile = $this->calculate_percentile($userrate, $peeravg, 100);

        return (object)[
            'user_value' => round($userrate, 1),
            'peer_avg' => round($peeravg, 1),
            'percentile' => $percentile
        ];
    }

    /**
     * Compare engagement score with peers.
     *
     * @param int $userid User ID
     * @param array $courseids Course IDs
     * @return object Comparison data
     */
    protected function compare_engagement($userid, $courseids) {
        global $DB;

        $since = time() - (7 * 24 * 3600);

        // Get user's engagement (activity count).
        $userengagement = $DB->count_records_select('logstore_standard_log',
            'userid = :userid AND timecreated > :since AND action IN (\'viewed\', \'submitted\', \'posted\', \'updated\')',
            ['userid' => $userid, 'since' => $since]
        );

        // Get peer average.
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $sql = "SELECT AVG(activity_count) as avg_engagement
                FROM (
                    SELECT l.userid, COUNT(*) as activity_count
                    FROM {logstore_standard_log} l
                    JOIN {user_enrolments} ue ON ue.userid = l.userid
                    JOIN {enrol} e ON e.id = ue.enrolid
                    WHERE e.courseid $coursesql
                    AND l.userid != :userid
                    AND l.timecreated > :since
                    AND l.action IN ('viewed', 'submitted', 'posted', 'updated')
                    GROUP BY l.userid
                ) subquery";

        $params = array_merge($courseparams, [
            'userid' => $userid,
            'since' => $since
        ]);

        $result = $DB->get_record_sql($sql, $params);
        $peeravg = $result->avg_engagement ?? 0;

        $percentile = $this->calculate_percentile($userengagement, $peeravg, 50);

        return (object)[
            'user_value' => $userengagement,
            'peer_avg' => round($peeravg, 1),
            'percentile' => $percentile
        ];
    }

    /**
     * Compare grade performance with peers.
     *
     * @param int $userid User ID
     * @param array $courseids Course IDs
     * @return object Comparison data
     */
    protected function compare_grades($userid, $courseids) {
        global $DB;

        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // Get user's grade average.
        $sql = "SELECT AVG(gg.finalgrade / gi.grademax * 100) as grade_avg
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gg.userid = :userid
                AND gi.courseid $coursesql
                AND gi.itemtype = 'mod'
                AND gg.finalgrade IS NOT NULL";

        $params = array_merge($courseparams, ['userid' => $userid]);
        $userdata = $DB->get_record_sql($sql, $params);
        $useravg = $userdata->grade_avg ?? 0;

        // Get peer average.
        $sql = "SELECT AVG(grade_avg) as peer_avg
                FROM (
                    SELECT gg.userid, AVG(gg.finalgrade / gi.grademax * 100) as grade_avg
                    FROM {grade_grades} gg
                    JOIN {grade_items} gi ON gi.id = gg.itemid
                    JOIN {user_enrolments} ue ON ue.userid = gg.userid
                    JOIN {enrol} e ON e.id = ue.enrolid
                    WHERE e.courseid $coursesql
                    AND gg.userid != :userid
                    AND gi.itemtype = 'mod'
                    AND gg.finalgrade IS NOT NULL
                    GROUP BY gg.userid
                    HAVING COUNT(*) > 0
                ) subquery";

        $result = $DB->get_record_sql($sql, $params);
        $peeravg = $result->peer_avg ?? 0;

        $percentile = $this->calculate_percentile($useravg, $peeravg, 100);

        return (object)[
            'user_value' => round($useravg, 1),
            'peer_avg' => round($peeravg, 1),
            'percentile' => $percentile
        ];
    }

    /**
     * Calculate percentile position.
     *
     * @param float $uservalue User's value
     * @param float $peeravg Peer average
     * @param float $maxvalue Maximum possible value
     * @return int Percentile (0-100)
     */
    protected function calculate_percentile($uservalue, $peeravg, $maxvalue) {
        if ($peeravg == 0 || $maxvalue == 0) {
            return 50;
        }

        // Simple percentile calculation based on deviation from average.
        $deviation = ($uservalue - $peeravg) / $maxvalue;
        $percentile = 50 + ($deviation * 100);

        // Clamp between 0 and 100.
        return max(0, min(100, round($percentile)));
    }

    /**
     * Calculate overall percentile from all metrics.
     *
     * @param object $comparison Comparison data
     * @return int Overall percentile
     */
    protected function calculate_overall_percentile($comparison) {
        $percentiles = [
            $comparison->login_frequency->percentile,
            $comparison->assignment_completion->percentile,
            $comparison->engagement_score->percentile,
            $comparison->grade_performance->percentile
        ];

        return round(array_sum($percentiles) / count($percentiles));
    }

    /**
     * Get performance category based on percentile.
     *
     * @param int $percentile Overall percentile
     * @return string Category
     */
    protected function get_performance_category($percentile) {
        if ($percentile >= 90) {
            return 'top';
        } else if ($percentile >= 75) {
            return 'above_average';
        } else if ($percentile >= 50) {
            return 'average';
        } else if ($percentile >= 25) {
            return 'below_average';
        } else {
            return 'needs_improvement';
        }
    }

    /**
     * Get visual representation of comparison.
     *
     * @param object $comparison Comparison data
     * @return array Chart data
     */
    public function get_comparison_chart_data($comparison) {
        return [
            'labels' => [
                get_string('loginfrequency', 'local_student_monitor'),
                get_string('assignmentcompletion', 'local_student_monitor'),
                get_string('engagement', 'local_student_monitor'),
                get_string('gradeperformance', 'local_student_monitor')
            ],
            'user_data' => [
                $comparison->login_frequency->percentile,
                $comparison->assignment_completion->percentile,
                $comparison->engagement_score->percentile,
                $comparison->grade_performance->percentile
            ]
        ];
    }
}
