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
 * External API for getting leaderboard data.
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
 * External API for getting leaderboard data.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_leaderboard extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'limit' => new external_value(PARAM_INT, 'Number of entries to return', VALUE_DEFAULT, 50),
            'period' => new external_value(PARAM_TEXT, 'Period (all, month, week)', VALUE_DEFAULT, 'all')
        ]);
    }

    /**
     * Get leaderboard data.
     *
     * @param int $limit Number of entries
     * @param string $period Period
     * @return array Leaderboard data
     */
    public static function execute($limit = 50, $period = 'all') {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'limit' => $limit,
            'period' => $period
        ]);

        // Check permissions.
        $context = \context_system::instance();
        self::validate_context($context);

        // Get gamification manager.
        $gamificationmanager = new \local_student_monitor\manager\gamification_manager();

        // Get leaderboard.
        $leaderboard = $gamificationmanager->get_leaderboard($params['limit'], $params['period']);

        $result = [];
        $rank = 1;

        foreach ($leaderboard as $entry) {
            $user = $DB->get_record('user', ['id' => $entry->userid]);

            if ($user) {
                $achievementcount = $DB->count_records('local_sm_achievements', ['userid' => $entry->userid]);

                $result[] = [
                    'rank' => $rank,
                    'userid' => (int)$entry->userid,
                    'fullname' => fullname($user),
                    'total_points' => (int)$entry->total_points,
                    'level' => (int)$entry->level,
                    'current_streak' => (int)$entry->current_streak,
                    'achievements_count' => $achievementcount
                ];

                $rank++;
            }
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'rank' => new external_value(PARAM_INT, 'Rank position'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'fullname' => new external_value(PARAM_TEXT, 'Full name'),
                'total_points' => new external_value(PARAM_INT, 'Total points'),
                'level' => new external_value(PARAM_INT, 'Level'),
                'current_streak' => new external_value(PARAM_INT, 'Current streak days'),
                'achievements_count' => new external_value(PARAM_INT, 'Number of achievements')
            ])
        );
    }
}
