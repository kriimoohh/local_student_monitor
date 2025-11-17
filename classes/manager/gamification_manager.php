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
 * Gamification manager for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Gamification manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gamification_manager {

    /**
     * Achievement definitions.
     */
    const ACHIEVEMENTS = [
        'first_login' => ['points' => 10, 'badge' => 'beginner', 'name' => 'Premier pas'],
        'week_streak' => ['points' => 50, 'badge' => 'consistent', 'name' => 'Semaine assidue'],
        'month_streak' => ['points' => 200, 'badge' => 'dedicated', 'name' => 'Mois complet'],
        'all_assignments' => ['points' => 100, 'badge' => 'achiever', 'name' => 'Tous les devoirs'],
        'early_submitter' => ['points' => 30, 'badge' => 'punctual', 'name' => 'Soumission anticipée'],
        'helper' => ['points' => 75, 'badge' => 'community', 'name' => 'Entraide'],
        'improvement' => ['points' => 150, 'badge' => 'progress', 'name' => 'Progression'],
        'risk_recovery' => ['points' => 250, 'badge' => 'champion', 'name' => 'Remontée spectaculaire']
    ];

    /**
     * Award points to a student.
     *
     * @param int $userid User ID
     * @param int $points Points to award
     * @param string $reason Reason for points
     * @return int New total points
     */
    public function award_points($userid, $points, $reason) {
        global $DB;

        // Get or create student gamification record.
        $record = $this->get_student_gamification($userid);

        $record->total_points += $points;
        $record->level = $this->calculate_level($record->total_points);
        $record->timemodified = time();

        if ($record->id) {
            $DB->update_record('local_sm_gamification', $record);
        } else {
            $record->userid = $userid;
            $record->timecreated = time();
            $record->id = $DB->insert_record('local_sm_gamification', $record);
        }

        // Log points transaction.
        $this->log_points_transaction($userid, $points, $reason);

        // Check for new achievements.
        $this->check_achievements($userid);

        return $record->total_points;
    }

    /**
     * Get student gamification data.
     *
     * @param int $userid User ID
     * @return object Gamification record
     */
    public function get_student_gamification($userid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_gamification')) {
            $record = $DB->get_record('local_sm_gamification', ['userid' => $userid]);
            if ($record) {
                return $record;
            }
        }

        // Create default record.
        $record = new \stdClass();
        $record->id = 0;
        $record->userid = $userid;
        $record->total_points = 0;
        $record->level = 1;
        $record->current_streak = 0;
        $record->longest_streak = 0;
        $record->last_activity = 0;

        return $record;
    }

    /**
     * Calculate level from points.
     *
     * @param int $points Total points
     * @return int Level
     */
    protected function calculate_level($points) {
        // Level progression: 100 points per level, with increasing requirements.
        $level = 1;
        $threshold = 0;
        $increment = 100;

        while ($points >= $threshold + $increment) {
            $threshold += $increment;
            $level++;
            $increment = $increment * 1.2; // 20% increase per level.
        }

        return $level;
    }

    /**
     * Log points transaction.
     *
     * @param int $userid User ID
     * @param int $points Points
     * @param string $reason Reason
     */
    protected function log_points_transaction($userid, $points, $reason) {
        global $DB;

        $transaction = new \stdClass();
        $transaction->userid = $userid;
        $transaction->points = $points;
        $transaction->reason = $reason;
        $transaction->timecreated = time();

        if ($DB->get_manager()->table_exists('local_sm_points_log')) {
            $DB->insert_record('local_sm_points_log', $transaction);
        }
    }

    /**
     * Check and award achievements.
     *
     * @param int $userid User ID
     */
    public function check_achievements($userid) {
        global $DB;

        $gamification = $this->get_student_gamification($userid);
        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);

        if (!$tracking) {
            return;
        }

        // Check first login.
        $this->check_achievement($userid, 'first_login', true);

        // Check week streak.
        if ($gamification->current_streak >= 7) {
            $this->check_achievement($userid, 'week_streak', true);
        }

        // Check month streak.
        if ($gamification->current_streak >= 30) {
            $this->check_achievement($userid, 'month_streak', true);
        }

        // Check all assignments completed.
        if ($tracking->missing_assignments == 0 && $tracking->intervention_count > 0) {
            $this->check_achievement($userid, 'all_assignments', true);
        }

        // Check risk recovery.
        $this->check_risk_recovery($userid);
    }

    /**
     * Check specific achievement.
     *
     * @param int $userid User ID
     * @param string $achievementkey Achievement key
     * @param bool $condition Achievement condition met
     * @return bool Achievement awarded
     */
    protected function check_achievement($userid, $achievementkey, $condition) {
        global $DB;

        if (!$condition) {
            return false;
        }

        // Check if already awarded.
        if ($this->has_achievement($userid, $achievementkey)) {
            return false;
        }

        // Award achievement.
        $achievement = self::ACHIEVEMENTS[$achievementkey];

        $record = new \stdClass();
        $record->userid = $userid;
        $record->achievement_key = $achievementkey;
        $record->achievement_name = $achievement['name'];
        $record->badge = $achievement['badge'];
        $record->points_awarded = $achievement['points'];
        $record->timecreated = time();

        if ($DB->get_manager()->table_exists('local_sm_achievements')) {
            $DB->insert_record('local_sm_achievements', $record);
        }

        // Award points.
        $this->award_points($userid, $achievement['points'], 'Achievement: ' . $achievement['name']);

        // Send notification.
        $this->notify_achievement($userid, $achievement);

        return true;
    }

    /**
     * Check if user has achievement.
     *
     * @param int $userid User ID
     * @param string $achievementkey Achievement key
     * @return bool Has achievement
     */
    protected function has_achievement($userid, $achievementkey) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_achievements')) {
            return $DB->record_exists('local_sm_achievements', [
                'userid' => $userid,
                'achievement_key' => $achievementkey
            ]);
        }

        return false;
    }

    /**
     * Check risk recovery achievement.
     *
     * @param int $userid User ID
     */
    protected function check_risk_recovery($userid) {
        global $DB;

        // Check if student went from CRITIQUE/ÉLEVÉ to FAIBLE/MOYEN.
        $logs = $DB->get_records_sql("
            SELECT *
            FROM {local_sm_logs}
            WHERE userid = :userid
              AND action = 'risk_level_changed'
            ORDER BY timecreated DESC
            LIMIT 5
        ", ['userid' => $userid]);

        if (count($logs) < 2) {
            return;
        }

        $logs = array_values($logs);
        $latest = json_decode($logs[0]->details);
        $previous = json_decode($logs[1]->details);

        if ($latest && $previous) {
            if (in_array($previous->old_level, ['CRITIQUE', 'ÉLEVÉ']) &&
                in_array($latest->new_level, ['FAIBLE', 'MOYEN'])) {
                $this->check_achievement($userid, 'risk_recovery', true);
            }
        }
    }

    /**
     * Notify student of new achievement.
     *
     * @param int $userid User ID
     * @param array $achievement Achievement data
     */
    protected function notify_achievement($userid, $achievement) {
        $notificationmanager = new notification_manager();

        $subject = get_string('achievementunlocked', 'local_student_monitor');
        $message = get_string('achievementmessage', 'local_student_monitor', [
            'name' => $achievement['name'],
            'points' => $achievement['points']
        ]);

        $notificationmanager->create_notification(
            $userid,
            'achievement',
            $subject,
            $message,
            0,
            ['moodle']
        );
    }

    /**
     * Update student activity streak.
     *
     * @param int $userid User ID
     */
    public function update_streak($userid) {
        global $DB;

        $gamification = $this->get_student_gamification($userid);

        $today = strtotime('today');
        $lastactivity = $gamification->last_activity;

        if ($lastactivity >= $today) {
            // Already updated today.
            return;
        }

        $yesterday = strtotime('yesterday');

        if ($lastactivity >= $yesterday && $lastactivity < $today) {
            // Streak continues.
            $gamification->current_streak++;
        } else {
            // Streak broken.
            $gamification->current_streak = 1;
        }

        // Update longest streak.
        if ($gamification->current_streak > $gamification->longest_streak) {
            $gamification->longest_streak = $gamification->current_streak;
        }

        $gamification->last_activity = time();
        $gamification->timemodified = time();

        if ($gamification->id) {
            $DB->update_record('local_sm_gamification', $gamification);
        } else {
            $gamification->userid = $userid;
            $gamification->timecreated = time();
            $DB->insert_record('local_sm_gamification', $gamification);
        }

        // Award streak points.
        if ($gamification->current_streak % 7 == 0) {
            $this->award_points($userid, 25, 'Streak: ' . $gamification->current_streak . ' days');
        }
    }

    /**
     * Get leaderboard.
     *
     * @param int $limit Number of top students
     * @param string $period Time period (week, month, all)
     * @return array Leaderboard data
     */
    public function get_leaderboard($limit = 10, $period = 'all') {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_sm_gamification')) {
            return [];
        }

        $sql = "SELECT g.*, u.firstname, u.lastname, u.picture, u.imagealt
                FROM {local_sm_gamification} g
                JOIN {user} u ON u.id = g.userid
                ORDER BY g.total_points DESC";

        $records = $DB->get_records_sql($sql, [], 0, $limit);

        $leaderboard = [];
        $rank = 1;
        foreach ($records as $record) {
            $entry = new \stdClass();
            $entry->rank = $rank++;
            $entry->userid = $record->userid;
            $entry->fullname = $record->firstname . ' ' . $record->lastname;
            $entry->points = $record->total_points;
            $entry->level = $record->level;
            $entry->streak = $record->current_streak;
            $entry->picture = $record->picture;
            $leaderboard[] = $entry;
        }

        return $leaderboard;
    }

    /**
     * Get student achievements.
     *
     * @param int $userid User ID
     * @return array Achievement records
     */
    public function get_student_achievements($userid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_achievements')) {
            return $DB->get_records('local_sm_achievements', ['userid' => $userid], 'timecreated DESC');
        }

        return [];
    }

    /**
     * Get gamification statistics.
     *
     * @return object Statistics
     */
    public function get_statistics() {
        global $DB;

        $stats = new \stdClass();

        if (!$DB->get_manager()->table_exists('local_sm_gamification')) {
            $stats->total_students = 0;
            $stats->total_points = 0;
            $stats->total_achievements = 0;
            $stats->avg_level = 0;
            return $stats;
        }

        $result = $DB->get_record_sql("
            SELECT
                COUNT(*) as total_students,
                SUM(total_points) as total_points,
                AVG(level) as avg_level,
                MAX(level) as max_level
            FROM {local_sm_gamification}
        ");

        $stats->total_students = $result->total_students ?? 0;
        $stats->total_points = $result->total_points ?? 0;
        $stats->avg_level = round($result->avg_level ?? 0, 1);
        $stats->max_level = $result->max_level ?? 0;

        if ($DB->get_manager()->table_exists('local_sm_achievements')) {
            $stats->total_achievements = $DB->count_records('local_sm_achievements');
        } else {
            $stats->total_achievements = 0;
        }

        return $stats;
    }
}
