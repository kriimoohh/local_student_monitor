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
 * External API for getting campaign statistics.
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

/**
 * External API for getting campaign statistics.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_campaign_stats extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'campaignid' => new external_value(PARAM_INT, 'Campaign ID')
        ]);
    }

    /**
     * Get campaign statistics.
     *
     * @param int $campaignid Campaign ID
     * @return array Campaign statistics (JSON encoded)
     */
    public static function execute($campaignid) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['campaignid' => $campaignid]);

        // Check permissions.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/student_monitor:managesettings', $context);

        // Get campaign manager.
        $campaignmanager = new \local_student_monitor\manager\email_campaign_manager();

        // Get statistics.
        $stats = $campaignmanager->get_campaign_statistics($params['campaignid']);

        return [
            'stats' => json_encode($stats)
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new \external_single_structure([
            'stats' => new external_value(PARAM_RAW, 'Campaign statistics (JSON)')
        ]);
    }
}
