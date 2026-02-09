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
 * External API for getting student statistics.
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
 * External API for getting student statistics.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_student_stats extends external_api {

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
     * Get student statistics.
     *
     * @param int $userid User ID (0 = current user)
     * @return array Student statistics
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

        // Check if user can view this data.
        if ($params['userid'] != $USER->id &&
            !has_capability('local/student_monitor:viewreports', $context)) {
            throw new \moodle_exception('nopermissions');
        }

        // Get tracking data.
        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $params['userid']]);

        if (!$tracking) {
            return [
                'userid' => $params['userid'],
                'risk_level' => 'UNKNOWN',
                'inactivity_days' => 0,
                'missing_activities' => 0,
                'notification_count' => 0,
                'last_updated' => 0
            ];
        }

        return [
            'userid' => $tracking->userid,
            'risk_level' => $tracking->risk_level,
            'inactivity_days' => (int)$tracking->inactivity_days,
            'missing_activities' => (int)$tracking->missing_activities,
            'notification_count' => (int)$tracking->notification_count,
            'last_updated' => (int)$tracking->timemodified
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
            'risk_level' => new external_value(PARAM_TEXT, 'Risk level'),
            'inactivity_days' => new external_value(PARAM_INT, 'Inactivity days'),
            'missing_activities' => new external_value(PARAM_INT, 'Missing activities'),
            'notification_count' => new external_value(PARAM_INT, 'Notification count'),
            'last_updated' => new external_value(PARAM_INT, 'Last updated timestamp')
        ]);
    }
}
