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
 * External API for getting gamification data.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

/**
 * External API for getting gamification data.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_gamification_data extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Get gamification data.
     *
     * @param int $userid User ID (0 = current user)
     * @return array Gamification data
     */
    public static function execute($userid = 0) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);

        // Use current user if not specified.
        if ($params['userid'] == 0) {
            $params['userid'] = $USER->id;
        }

        // Check permissions.
        $context = \context_system::instance();
        self::validate_context($context);

        // Get gamification manager.
        $gamificationmanager = new \local_student_monitor\manager\gamification_manager();

        // Get user stats.
        $stats = $gamificationmanager->get_user_gamification_stats($params['userid']);

        if (!$stats) {
            $stats = (object)[
                'userid' => $params['userid'],
                'total_points' => 0,
                'level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity' => 0
            ];
        }

        // Get achievements.
        $achievements = $DB->get_records('local_sm_achievements', ['userid' => $params['userid']],
                                        'timecreated DESC');

        $achievementlist = [];
        foreach ($achievements as $achievement) {
            $achievementdata = \local_student_monitor\manager\gamification_manager::ACHIEVEMENTS[$achievement->achievement_key] ?? null;

            if ($achievementdata) {
                $achievementlist[] = [
                    'key' => $achievement->achievement_key,
                    'name' => $achievementdata['name'],
                    'badge' => $achievementdata['badge'],
                    'points' => (int)$achievement->points_awarded,
                    'earned_date' => (int)$achievement->timecreated
                ];
            }
        }

        // Get leaderboard position.
        $leaderboard = $gamificationmanager->get_leaderboard(1000, 'all');
        $position = 0;
        $rank = 1;
        foreach ($leaderboard as $entry) {
            if ($entry->userid == $params['userid']) {
                $position = $rank;
                break;
            }
            $rank++;
        }

        // Calculate next level info.
        $nextlevelpoints = 100 * pow(1.2, $stats->level);
        $progress = ($stats->total_points / $nextlevelpoints) * 100;

        return [
            'userid' => (int)$stats->userid,
            'total_points' => (int)$stats->total_points,
            'level' => (int)$stats->level,
            'current_streak' => (int)$stats->current_streak,
            'longest_streak' => (int)$stats->longest_streak,
            'leaderboard_position' => $position,
            'next_level_points' => (int)round($nextlevelpoints),
            'progress_percentage' => min(round($progress, 2), 100),
            'achievements' => $achievementlist
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'total_points' => new external_value(PARAM_INT, 'Total points'),
            'level' => new external_value(PARAM_INT, 'Current level'),
            'current_streak' => new external_value(PARAM_INT, 'Current streak days'),
            'longest_streak' => new external_value(PARAM_INT, 'Longest streak days'),
            'leaderboard_position' => new external_value(PARAM_INT, 'Position in leaderboard'),
            'next_level_points' => new external_value(PARAM_INT, 'Points needed for next level'),
            'progress_percentage' => new external_value(PARAM_FLOAT, 'Progress to next level (%)'),
            'achievements' => new external_multiple_structure(
                new external_single_structure([
                    'key' => new external_value(PARAM_TEXT, 'Achievement key'),
                    'name' => new external_value(PARAM_TEXT, 'Achievement name'),
                    'badge' => new external_value(PARAM_TEXT, 'Badge emoji'),
                    'points' => new external_value(PARAM_INT, 'Points awarded'),
                    'earned_date' => new external_value(PARAM_INT, 'Date earned (timestamp)')
                ])
            )
        ]);
    }
}
