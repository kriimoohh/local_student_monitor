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
 * External API for searching users.
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
 * External API for searching users.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_users extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Search query'),
            'limitnum' => new external_value(PARAM_INT, 'Maximum number of results', VALUE_DEFAULT, 100)
        ]);
    }

    /**
     * Search for users.
     *
     * @param string $query Search query
     * @param int $limitnum Maximum number of results
     * @return array List of users matching the search
     */
    public static function execute($query, $limitnum = 100) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'limitnum' => $limitnum
        ]);

        // Check permissions.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/student_monitor:sendmanual', $context);

        // Search for users (students only).
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.username
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE u.deleted = 0
                   AND u.suspended = 0
                   AND r.shortname = 'student'
                   AND (" . $DB->sql_like('u.firstname', ':firstname', false) . "
                        OR " . $DB->sql_like('u.lastname', ':lastname', false) . "
                        OR " . $DB->sql_like('u.email', ':email', false) . "
                        OR " . $DB->sql_like('u.username', ':username', false) . "
                        OR " . $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname'), ':fullname', false) . ")
              ORDER BY u.firstname, u.lastname
                 LIMIT :limitnum";

        $searchparam = '%' . $DB->sql_like_escape($params['query']) . '%';

        $users = $DB->get_records_sql($sql, [
            'firstname' => $searchparam,
            'lastname' => $searchparam,
            'email' => $searchparam,
            'username' => $searchparam,
            'fullname' => $searchparam,
            'limitnum' => $params['limitnum']
        ]);

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'fullname' => fullname($user),
                'email' => $user->email
            ];
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
                'id' => new external_value(PARAM_INT, 'User ID'),
                'fullname' => new external_value(PARAM_TEXT, 'User full name'),
                'email' => new external_value(PARAM_TEXT, 'User email')
            ])
        );
    }
}
