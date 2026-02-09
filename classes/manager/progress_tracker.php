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
 * Student progress tracking and goal management.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Progress tracker manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_tracker {

    /**
     * Goal types.
     */
    const GOAL_GRADE = 'grade';
    const GOAL_ATTENDANCE = 'attendance';
    const GOAL_ASSIGNMENT = 'assignment';
    const GOAL_ENGAGEMENT = 'engagement';
    const GOAL_CUSTOM = 'custom';

    /**
     * Goal status.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Create a new goal for a student.
     *
     * @param int $userid User ID
     * @param string $type Goal type
     * @param string $title Goal title
     * @param string $description Goal description
     * @param float $targetvalue Target value
     * @param int $deadline Deadline timestamp
     * @return int Goal ID
     */
    public function create_goal($userid, $type, $title, $description, $targetvalue, $deadline) {
        global $DB;

        $goal = new \stdClass();
        $goal->userid = $userid;
        $goal->type = $type;
        $goal->title = $title;
        $goal->description = $description;
        $goal->target_value = $targetvalue;
        $goal->current_value = 0;
        $goal->deadline = $deadline;
        $goal->status = self::STATUS_ACTIVE;
        $goal->timecreated = time();
        $goal->timemodified = time();

        return $DB->insert_record('local_sm_goals', $goal);
    }

    /**
     * Update goal progress.
     *
     * @param int $goalid Goal ID
     * @param float $currentvalue Current value
     * @return bool Success
     */
    public function update_goal_progress($goalid, $currentvalue) {
        global $DB;

        $goal = $DB->get_record('local_sm_goals', ['id' => $goalid], '*', MUST_EXIST);

        $goal->current_value = $currentvalue;
        $goal->timemodified = time();

        // Check if goal is completed.
        if ($currentvalue >= $goal->target_value && $goal->status == self::STATUS_ACTIVE) {
            $goal->status = self::STATUS_COMPLETED;
            $goal->timecompleted = time();

            // Award gamification points.
            $this->award_goal_completion_points($goal->userid, $goal);
        }

        // Check if deadline passed without completion.
        if ($goal->deadline > 0 && time() > $goal->deadline && $goal->status == self::STATUS_ACTIVE) {
            $goal->status = self::STATUS_FAILED;
        }

        return $DB->update_record('local_sm_goals', $goal);
    }

    /**
     * Get all goals for a user.
     *
     * @param int $userid User ID
     * @param string $status Filter by status (null = all)
     * @return array Goals
     */
    public function get_user_goals($userid, $status = null) {
        global $DB;

        $params = ['userid' => $userid];

        if ($status) {
            $params['status'] = $status;
        }

        return $DB->get_records('local_sm_goals', $params, 'timecreated DESC');
    }

    /**
     * Get goal statistics for a user.
     *
     * @param int $userid User ID
     * @return object Statistics
     */
    public function get_goal_statistics($userid) {
        global $DB;

        $stats = new \stdClass();

        $stats->total_goals = $DB->count_records('local_sm_goals', ['userid' => $userid]);
        $stats->active_goals = $DB->count_records('local_sm_goals', [
            'userid' => $userid,
            'status' => self::STATUS_ACTIVE
        ]);
        $stats->completed_goals = $DB->count_records('local_sm_goals', [
            'userid' => $userid,
            'status' => self::STATUS_COMPLETED
        ]);

        $stats->completion_rate = $stats->total_goals > 0 ?
            ($stats->completed_goals / $stats->total_goals) * 100 : 0;

        // Get average completion time.
        $sql = "SELECT AVG(timecompleted - timecreated) as avg_time
                FROM {local_sm_goals}
                WHERE userid = :userid
                AND status = :status
                AND timecompleted IS NOT NULL";

        $result = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'status' => self::STATUS_COMPLETED
        ]);

        $stats->avg_completion_days = $result->avg_time ? round($result->avg_time / (24 * 3600), 1) : 0;

        return $stats;
    }

    /**
     * Award points for goal completion.
     *
     * @param int $userid User ID
     * @param object $goal Goal object
     */
    protected function award_goal_completion_points($userid, $goal) {
        $gamificationmanager = new gamification_manager();

        // Base points.
        $points = 50;

        // Bonus for completing before deadline.
        if ($goal->deadline > 0 && time() < $goal->deadline) {
            $timeremaining = $goal->deadline - time();
            $totalduration = $goal->deadline - $goal->timecreated;

            if ($totalduration > 0) {
                $percentageremaining = ($timeremaining / $totalduration) * 100;

                if ($percentageremaining > 50) {
                    $points += 25; // Completed with more than 50% time remaining.
                } else if ($percentageremaining > 25) {
                    $points += 10; // Completed with 25-50% time remaining.
                }
            }
        }

        $gamificationmanager->award_points($userid, $points,
            get_string('goal_completed', 'local_student_monitor', $goal->title));
    }

    /**
     * Track daily progress snapshot.
     *
     * @param int $userid User ID
     * @return int Snapshot ID
     */
    public function track_daily_progress($userid) {
        global $DB;

        $snapshot = new \stdClass();
        $snapshot->userid = $userid;
        $snapshot->date = strtotime('today');

        // Get tracking data.
        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);

        if ($tracking) {
            $snapshot->risk_level = $tracking->risk_level;
            $snapshot->inactivity_days = $tracking->inactivity_days;
            $snapshot->missing_activities = $tracking->missing_activities;
        } else {
            $snapshot->risk_level = 'UNKNOWN';
            $snapshot->inactivity_days = 0;
            $snapshot->missing_activities = 0;
        }

        // Get gamification data.
        $gamificationmanager = new gamification_manager();
        $gamstats = $gamificationmanager->get_user_gamification_stats($userid);

        if ($gamstats) {
            $snapshot->total_points = $gamstats->total_points;
            $snapshot->level = $gamstats->level;
            $snapshot->streak = $gamstats->current_streak;
        } else {
            $snapshot->total_points = 0;
            $snapshot->level = 1;
            $snapshot->streak = 0;
        }

        // Get grade average.
        $sql = "SELECT AVG(gg.finalgrade / gi.grademax * 100) as grade_avg
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gg.userid = :userid
                AND gi.itemtype = 'mod'
                AND gg.finalgrade IS NOT NULL";

        $gradedata = $DB->get_record_sql($sql, ['userid' => $userid]);
        $snapshot->grade_average = $gradedata->grade_avg ?? 0;

        // Get login count (last 7 days).
        $weekago = time() - (7 * 24 * 3600);
        $snapshot->weekly_logins = $DB->count_records_select('logstore_standard_log',
            'userid = :userid AND action = :action AND timecreated > :since',
            ['userid' => $userid, 'action' => 'loggedin', 'since' => $weekago]
        );

        $snapshot->timecreated = time();

        // Check if snapshot for today already exists.
        $existing = $DB->get_record('local_sm_progress_snapshots', [
            'userid' => $userid,
            'date' => $snapshot->date
        ]);

        if ($existing) {
            $snapshot->id = $existing->id;
            $DB->update_record('local_sm_progress_snapshots', $snapshot);
            return $existing->id;
        } else {
            return $DB->insert_record('local_sm_progress_snapshots', $snapshot);
        }
    }

    /**
     * Get progress history for a user.
     *
     * @param int $userid User ID
     * @param int $days Number of days to retrieve
     * @return array Snapshots
     */
    public function get_progress_history($userid, $days = 30) {
        global $DB;

        $since = time() - ($days * 24 * 3600);

        return $DB->get_records_select('local_sm_progress_snapshots',
            'userid = :userid AND timecreated > :since',
            ['userid' => $userid, 'since' => $since],
            'date ASC'
        );
    }

    /**
     * Get progress trends.
     *
     * @param int $userid User ID
     * @return object Trend data
     */
    public function get_progress_trends($userid) {
        global $DB;

        $trends = new \stdClass();

        // Get last 30 days of data.
        $history = $this->get_progress_history($userid, 30);

        if (empty($history)) {
            return $this->get_empty_trends();
        }

        $snapshots = array_values($history);
        $count = count($snapshots);

        // Calculate trends.
        $trends->grade_trend = $this->calculate_trend($snapshots, 'grade_average');
        $trends->points_trend = $this->calculate_trend($snapshots, 'total_points');
        $trends->login_trend = $this->calculate_trend($snapshots, 'weekly_logins');
        $trends->risk_trend = $this->calculate_risk_trend($snapshots);

        // Current vs previous period comparison.
        $midpoint = floor($count / 2);

        $firsthalf = array_slice($snapshots, 0, $midpoint);
        $secondhalf = array_slice($snapshots, $midpoint);

        $trends->grade_improvement = $this->calculate_improvement($firsthalf, $secondhalf, 'grade_average');
        $trends->points_improvement = $this->calculate_improvement($firsthalf, $secondhalf, 'total_points');

        return $trends;
    }

    /**
     * Get empty trends object.
     *
     * @return object Empty trends
     */
    protected function get_empty_trends() {
        $trends = new \stdClass();
        $trends->grade_trend = 'stable';
        $trends->points_trend = 'stable';
        $trends->login_trend = 'stable';
        $trends->risk_trend = 'stable';
        $trends->grade_improvement = 0;
        $trends->points_improvement = 0;

        return $trends;
    }

    /**
     * Calculate trend direction.
     *
     * @param array $snapshots Snapshots
     * @param string $field Field name
     * @return string Trend (improving, declining, stable)
     */
    protected function calculate_trend($snapshots, $field) {
        if (count($snapshots) < 2) {
            return 'stable';
        }

        $values = array_map(function($s) use ($field) {
            return $s->$field ?? 0;
        }, $snapshots);

        // Simple linear regression slope.
        $slope = $this->calculate_slope($values);

        if ($slope > 0.1) {
            return 'improving';
        } else if ($slope < -0.1) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    /**
     * Calculate risk trend.
     *
     * @param array $snapshots Snapshots
     * @return string Trend
     */
    protected function calculate_risk_trend($snapshots) {
        if (count($snapshots) < 2) {
            return 'stable';
        }

        $riskvalues = [
            'LOW' => 1,
            'MEDIUM' => 2,
            'HIGH' => 3,
            'CRITICAL' => 4
        ];

        $values = array_map(function($s) use ($riskvalues) {
            return $riskvalues[$s->risk_level] ?? 0;
        }, $snapshots);

        $slope = $this->calculate_slope($values);

        if ($slope < -0.1) {
            return 'improving'; // Risk decreasing.
        } else if ($slope > 0.1) {
            return 'declining'; // Risk increasing.
        } else {
            return 'stable';
        }
    }

    /**
     * Calculate slope using linear regression.
     *
     * @param array $values Values
     * @return float Slope
     */
    protected function calculate_slope($values) {
        $n = count($values);

        if ($n < 2) {
            return 0;
        }

        $sumx = 0;
        $sumy = 0;
        $sumxy = 0;
        $sumx2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $values[$i];

            $sumx += $x;
            $sumy += $y;
            $sumxy += ($x * $y);
            $sumx2 += ($x * $x);
        }

        $denominator = ($n * $sumx2) - ($sumx * $sumx);

        if ($denominator == 0) {
            return 0;
        }

        return (($n * $sumxy) - ($sumx * $sumy)) / $denominator;
    }

    /**
     * Calculate improvement percentage between two periods.
     *
     * @param array $firstperiod First period snapshots
     * @param array $secondperiod Second period snapshots
     * @param string $field Field name
     * @return float Improvement percentage
     */
    protected function calculate_improvement($firstperiod, $secondperiod, $field) {
        if (empty($firstperiod) || empty($secondperiod)) {
            return 0;
        }

        $firstavg = array_sum(array_map(function($s) use ($field) {
            return $s->$field ?? 0;
        }, $firstperiod)) / count($firstperiod);

        $secondavg = array_sum(array_map(function($s) use ($field) {
            return $s->$field ?? 0;
        }, $secondperiod)) / count($secondperiod);

        if ($firstavg == 0) {
            return 0;
        }

        return (($secondavg - $firstavg) / $firstavg) * 100;
    }

    /**
     * Get suggested goals based on user performance.
     *
     * @param int $userid User ID
     * @return array Suggested goals
     */
    public function get_suggested_goals($userid) {
        global $DB;

        $suggestions = [];

        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);

        if (!$tracking) {
            return [];
        }

        // Suggest assignment goal if missing activities.
        if ($tracking->missing_activities > 0) {
            $suggestions[] = (object)[
                'type' => self::GOAL_ASSIGNMENT,
                'title' => get_string('goal_complete_assignments', 'local_student_monitor'),
                'description' => get_string('goal_complete_assignments_desc', 'local_student_monitor'),
                'target_value' => $tracking->missing_activities,
                'suggested_deadline' => time() + (14 * 24 * 3600) // 2 weeks
            ];
        }

        // Suggest engagement goal if low activity.
        $weekago = time() - (7 * 24 * 3600);
        $weeklylogins = $DB->count_records_select('logstore_standard_log',
            'userid = :userid AND action = :action AND timecreated > :since',
            ['userid' => $userid, 'action' => 'loggedin', 'since' => $weekago]
        );

        if ($weeklylogins < 5) {
            $suggestions[] = (object)[
                'type' => self::GOAL_ENGAGEMENT,
                'title' => get_string('goal_increase_logins', 'local_student_monitor'),
                'description' => get_string('goal_increase_logins_desc', 'local_student_monitor'),
                'target_value' => 10,
                'suggested_deadline' => time() + (7 * 24 * 3600) // 1 week
            ];
        }

        return $suggestions;
    }
}
