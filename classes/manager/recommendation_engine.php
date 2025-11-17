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
 * AI-powered recommendation engine for students.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Recommendation engine manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recommendation_engine {

    /**
     * Recommendation types.
     */
    const TYPE_STUDY_HABIT = 'study_habit';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_COURSE_ACTIVITY = 'course_activity';
    const TYPE_PEER_LEARNING = 'peer_learning';
    const TYPE_RESOURCE = 'resource';
    const TYPE_TIME_MANAGEMENT = 'time_management';
    const TYPE_ENGAGEMENT = 'engagement';

    /**
     * Priority levels.
     */
    const PRIORITY_CRITICAL = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_MEDIUM = 3;
    const PRIORITY_LOW = 4;

    /**
     * Generate personalized recommendations for a student.
     *
     * @param int $userid User ID
     * @param int $limit Maximum number of recommendations
     * @return array Array of recommendation objects
     */
    public function generate_recommendations($userid, $limit = 10) {
        global $DB;

        $recommendations = [];

        // Get student tracking data.
        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);

        if (!$tracking) {
            return [];
        }

        // Get student performance data.
        $performance = $this->analyze_student_performance($userid);

        // Generate recommendations based on different factors.
        $recommendations = array_merge($recommendations, $this->recommend_study_habits($userid, $tracking, $performance));
        $recommendations = array_merge($recommendations, $this->recommend_assignments($userid, $tracking));
        $recommendations = array_merge($recommendations, $this->recommend_course_activities($userid, $performance));
        $recommendations = array_merge($recommendations, $this->recommend_peer_learning($userid, $performance));
        $recommendations = array_merge($recommendations, $this->recommend_time_management($userid, $tracking, $performance));
        $recommendations = array_merge($recommendations, $this->recommend_engagement($userid, $tracking, $performance));

        // Sort by priority and limit.
        usort($recommendations, function($a, $b) {
            if ($a->priority == $b->priority) {
                return $b->impact_score - $a->impact_score;
            }
            return $a->priority - $b->priority;
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Analyze student performance across all metrics.
     *
     * @param int $userid User ID
     * @return object Performance data
     */
    protected function analyze_student_performance($userid) {
        global $DB;

        $performance = new \stdClass();

        // Get login patterns.
        $sql = "SELECT COUNT(*) as login_count,
                       AVG(TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(timecreated), NOW())) as avg_hours_since_login
                FROM {logstore_standard_log}
                WHERE userid = :userid
                AND action = 'loggedin'
                AND timecreated > :since";

        $logindata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'since' => time() - (30 * 24 * 3600)
        ]);

        $performance->login_frequency = $logindata->login_count ?? 0;
        $performance->avg_login_gap = $logindata->avg_hours_since_login ?? 0;

        // Get assignment completion rate.
        $sql = "SELECT COUNT(DISTINCT a.id) as total_assignments,
                       COUNT(DISTINCT s.assignment) as completed_assignments
                FROM {assign} a
                JOIN {course_modules} cm ON cm.instance = a.id AND cm.module =
                    (SELECT id FROM {modules} WHERE name = 'assign')
                JOIN {course} c ON c.id = a.course
                JOIN {user_enrolments} ue ON ue.userid = :userid
                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = c.id
                LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = :userid2
                    AND s.status = 'submitted'
                WHERE a.duedate > :since";

        $assignmentdata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'userid2' => $userid,
            'since' => time() - (30 * 24 * 3600)
        ]);

        $performance->total_assignments = $assignmentdata->total_assignments ?? 0;
        $performance->completed_assignments = $assignmentdata->completed_assignments ?? 0;
        $performance->completion_rate = $performance->total_assignments > 0 ?
            ($performance->completed_assignments / $performance->total_assignments) * 100 : 0;

        // Get activity pattern (time of day most active).
        $sql = "SELECT HOUR(FROM_UNIXTIME(timecreated)) as hour,
                       COUNT(*) as activity_count
                FROM {logstore_standard_log}
                WHERE userid = :userid
                AND timecreated > :since
                GROUP BY HOUR(FROM_UNIXTIME(timecreated))
                ORDER BY activity_count DESC
                LIMIT 1";

        $activitydata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'since' => time() - (30 * 24 * 3600)
        ]);

        $performance->peak_activity_hour = $activitydata->hour ?? 12;

        // Get engagement score.
        $sql = "SELECT COUNT(*) as activity_count
                FROM {logstore_standard_log}
                WHERE userid = :userid
                AND timecreated > :since
                AND action IN ('viewed', 'submitted', 'posted', 'updated')";

        $engagementdata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'since' => time() - (7 * 24 * 3600)
        ]);

        $performance->weekly_engagement = $engagementdata->activity_count ?? 0;

        // Get grade average.
        $sql = "SELECT AVG(gg.finalgrade / gi.grademax * 100) as grade_average
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gg.userid = :userid
                AND gi.itemtype = 'mod'
                AND gg.finalgrade IS NOT NULL";

        $gradedata = $DB->get_record_sql($sql, ['userid' => $userid]);
        $performance->grade_average = $gradedata->grade_average ?? 0;

        return $performance;
    }

    /**
     * Recommend study habit improvements.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @param object $performance Performance data
     * @return array Recommendations
     */
    protected function recommend_study_habits($userid, $tracking, $performance) {
        $recommendations = [];

        // Check login frequency.
        if ($performance->login_frequency < 10) {
            $recommendations[] = (object)[
                'type' => self::TYPE_STUDY_HABIT,
                'priority' => self::PRIORITY_HIGH,
                'title' => get_string('rec_increase_login', 'local_student_monitor'),
                'description' => get_string('rec_increase_login_desc', 'local_student_monitor', [
                    'current' => $performance->login_frequency,
                    'target' => 15
                ]),
                'impact_score' => 85,
                'action_url' => null,
                'icon' => '🎯'
            ];
        }

        // Check study consistency.
        if ($performance->avg_login_gap > 48) {
            $recommendations[] = (object)[
                'type' => self::TYPE_STUDY_HABIT,
                'priority' => self::PRIORITY_MEDIUM,
                'title' => get_string('rec_study_consistency', 'local_student_monitor'),
                'description' => get_string('rec_study_consistency_desc', 'local_student_monitor'),
                'impact_score' => 75,
                'action_url' => null,
                'icon' => '📅'
            ];
        }

        // Recommend optimal study time.
        if ($performance->peak_activity_hour >= 22 || $performance->peak_activity_hour <= 6) {
            $recommendations[] = (object)[
                'type' => self::TYPE_STUDY_HABIT,
                'priority' => self::PRIORITY_LOW,
                'title' => get_string('rec_optimal_study_time', 'local_student_monitor'),
                'description' => get_string('rec_optimal_study_time_desc', 'local_student_monitor'),
                'impact_score' => 60,
                'action_url' => null,
                'icon' => '⏰'
            ];
        }

        return $recommendations;
    }

    /**
     * Recommend assignment-related actions.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @return array Recommendations
     */
    protected function recommend_assignments($userid, $tracking) {
        global $DB;

        $recommendations = [];

        // Get upcoming assignments.
        $sql = "SELECT a.id, a.name, a.duedate, c.fullname
                FROM {assign} a
                JOIN {course_modules} cm ON cm.instance = a.id AND cm.module =
                    (SELECT id FROM {modules} WHERE name = 'assign')
                JOIN {course} c ON c.id = a.course
                JOIN {user_enrolments} ue ON ue.userid = :userid
                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = c.id
                LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = :userid2
                    AND s.status = 'submitted'
                WHERE s.id IS NULL
                AND a.duedate > :now
                AND a.duedate < :threeDays
                ORDER BY a.duedate ASC
                LIMIT 3";

        $urgentassignments = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'userid2' => $userid,
            'now' => time(),
            'threeDays' => time() + (3 * 24 * 3600)
        ]);

        foreach ($urgentassignments as $assignment) {
            $recommendations[] = (object)[
                'type' => self::TYPE_ASSIGNMENT,
                'priority' => self::PRIORITY_CRITICAL,
                'title' => get_string('rec_urgent_assignment', 'local_student_monitor'),
                'description' => get_string('rec_urgent_assignment_desc', 'local_student_monitor', [
                    'name' => $assignment->name,
                    'course' => $assignment->fullname,
                    'duedate' => userdate($assignment->duedate)
                ]),
                'impact_score' => 100,
                'action_url' => new \moodle_url('/mod/assign/view.php', ['id' => $assignment->id]),
                'icon' => '⚠️'
            ];
        }

        return $recommendations;
    }

    /**
     * Recommend course activities.
     *
     * @param int $userid User ID
     * @param object $performance Performance data
     * @return array Recommendations
     */
    protected function recommend_course_activities($userid, $performance) {
        global $DB;

        $recommendations = [];

        // Find unviewed resources.
        $sql = "SELECT COUNT(*) as unviewed_count
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                JOIN {course} c ON c.id = cm.course
                JOIN {user_enrolments} ue ON ue.userid = :userid
                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = c.id
                LEFT JOIN {logstore_standard_log} l ON l.objectid = cm.id
                    AND l.userid = :userid2 AND l.action = 'viewed'
                WHERE l.id IS NULL
                AND cm.visible = 1
                AND m.name IN ('resource', 'page', 'url', 'book')";

        $unvieweddata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'userid2' => $userid
        ]);

        if ($unvieweddata->unviewed_count > 5) {
            $recommendations[] = (object)[
                'type' => self::TYPE_COURSE_ACTIVITY,
                'priority' => self::PRIORITY_MEDIUM,
                'title' => get_string('rec_explore_resources', 'local_student_monitor'),
                'description' => get_string('rec_explore_resources_desc', 'local_student_monitor', [
                    'count' => $unvieweddata->unviewed_count
                ]),
                'impact_score' => 70,
                'action_url' => new \moodle_url('/my/'),
                'icon' => '📚'
            ];
        }

        return $recommendations;
    }

    /**
     * Recommend peer learning opportunities.
     *
     * @param int $userid User ID
     * @param object $performance Performance data
     * @return array Recommendations
     */
    protected function recommend_peer_learning($userid, $performance) {
        global $DB;

        $recommendations = [];

        // Check forum participation.
        $sql = "SELECT COUNT(*) as post_count
                FROM {forum_posts} fp
                JOIN {forum_discussions} fd ON fd.id = fp.discussion
                JOIN {forum} f ON f.id = fd.forum
                WHERE fp.userid = :userid
                AND fp.created > :since";

        $forumdata = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'since' => time() - (30 * 24 * 3600)
        ]);

        if ($forumdata->post_count < 3) {
            $recommendations[] = (object)[
                'type' => self::TYPE_PEER_LEARNING,
                'priority' => self::PRIORITY_MEDIUM,
                'title' => get_string('rec_forum_participation', 'local_student_monitor'),
                'description' => get_string('rec_forum_participation_desc', 'local_student_monitor'),
                'impact_score' => 65,
                'action_url' => new \moodle_url('/mod/forum/index.php'),
                'icon' => '💬'
            ];
        }

        // Check if student could help others (high performer).
        if ($performance->grade_average > 75 && $forumdata->post_count < 5) {
            $recommendations[] = (object)[
                'type' => self::TYPE_PEER_LEARNING,
                'priority' => self::PRIORITY_LOW,
                'title' => get_string('rec_help_peers', 'local_student_monitor'),
                'description' => get_string('rec_help_peers_desc', 'local_student_monitor'),
                'impact_score' => 55,
                'action_url' => new \moodle_url('/mod/forum/index.php'),
                'icon' => '🤝'
            ];
        }

        return $recommendations;
    }

    /**
     * Recommend time management improvements.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @param object $performance Performance data
     * @return array Recommendations
     */
    protected function recommend_time_management($userid, $tracking, $performance) {
        $recommendations = [];

        // Check if student has many missing assignments.
        if ($tracking->missing_assignments > 3) {
            $recommendations[] = (object)[
                'type' => self::TYPE_TIME_MANAGEMENT,
                'priority' => self::PRIORITY_HIGH,
                'title' => get_string('rec_catch_up_plan', 'local_student_monitor'),
                'description' => get_string('rec_catch_up_plan_desc', 'local_student_monitor', [
                    'count' => $tracking->missing_assignments
                ]),
                'impact_score' => 90,
                'action_url' => new \moodle_url('/calendar/view.php'),
                'icon' => '📋'
            ];
        }

        // Recommend calendar usage.
        $recommendations[] = (object)[
            'type' => self::TYPE_TIME_MANAGEMENT,
            'priority' => self::PRIORITY_LOW,
            'title' => get_string('rec_use_calendar', 'local_student_monitor'),
            'description' => get_string('rec_use_calendar_desc', 'local_student_monitor'),
            'impact_score' => 50,
            'action_url' => new \moodle_url('/calendar/view.php'),
            'icon' => '📆'
        ];

        return $recommendations;
    }

    /**
     * Recommend engagement improvements.
     *
     * @param int $userid User ID
     * @param object $tracking Tracking data
     * @param object $performance Performance data
     * @return array Recommendations
     */
    protected function recommend_engagement($userid, $tracking, $performance) {
        $recommendations = [];

        // Low engagement check.
        if ($performance->weekly_engagement < 20) {
            $recommendations[] = (object)[
                'type' => self::TYPE_ENGAGEMENT,
                'priority' => self::PRIORITY_HIGH,
                'title' => get_string('rec_increase_engagement', 'local_student_monitor'),
                'description' => get_string('rec_increase_engagement_desc', 'local_student_monitor', [
                    'current' => $performance->weekly_engagement,
                    'target' => 40
                ]),
                'impact_score' => 80,
                'action_url' => new \moodle_url('/my/'),
                'icon' => '🚀'
            ];
        }

        // Gamification recommendation.
        if ($performance->weekly_engagement > 30) {
            $recommendations[] = (object)[
                'type' => self::TYPE_ENGAGEMENT,
                'priority' => self::PRIORITY_LOW,
                'title' => get_string('rec_check_leaderboard', 'local_student_monitor'),
                'description' => get_string('rec_check_leaderboard_desc', 'local_student_monitor'),
                'impact_score' => 45,
                'action_url' => new \moodle_url('/local/student_monitor/leaderboard.php'),
                'icon' => '🏆'
            ];
        }

        return $recommendations;
    }

    /**
     * Save a recommendation for future tracking.
     *
     * @param int $userid User ID
     * @param object $recommendation Recommendation object
     * @return int Recommendation ID
     */
    public function save_recommendation($userid, $recommendation) {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->type = $recommendation->type;
        $record->priority = $recommendation->priority;
        $record->title = $recommendation->title;
        $record->description = $recommendation->description;
        $record->impact_score = $recommendation->impact_score;
        $record->action_url = $recommendation->action_url ? $recommendation->action_url->out(false) : null;
        $record->status = 'active';
        $record->timecreated = time();

        return $DB->insert_record('local_sm_recommendations', $record);
    }

    /**
     * Mark a recommendation as completed.
     *
     * @param int $recommendationid Recommendation ID
     * @return bool Success
     */
    public function complete_recommendation($recommendationid) {
        global $DB;

        return $DB->set_field('local_sm_recommendations', 'status', 'completed', ['id' => $recommendationid]);
    }

    /**
     * Get active recommendations for a user.
     *
     * @param int $userid User ID
     * @return array Recommendations
     */
    public function get_active_recommendations($userid) {
        global $DB;

        return $DB->get_records('local_sm_recommendations', [
            'userid' => $userid,
            'status' => 'active'
        ], 'priority ASC, impact_score DESC');
    }
}
