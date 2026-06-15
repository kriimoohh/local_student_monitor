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
 * Web service definitions for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_student_monitor_get_student_stats' => [
        'classname' => 'local_student_monitor\external\get_student_stats',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Get student tracking statistics',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE]
    ],

    'local_student_monitor_search_users' => [
        'classname' => 'local_student_monitor\external\search_users',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Search for users to add as alert recipients',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/student_monitor:sendmanual'
    ]
];

$services = [
    'Student Monitor API' => [
        'functions' => [
            'local_student_monitor_get_student_stats'
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'student_monitor_api',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ]
];
