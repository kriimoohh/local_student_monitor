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
 * Event observer definitions for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Observe when a new course module (activity/resource) is created.
    [
        'eventname' => '\core\event\course_module_created',
        'callback' => '\local_student_monitor\observer::course_module_created',
        'priority' => 0,
    ],

    // Observe when a course module is updated.
    [
        'eventname' => '\core\event\course_module_updated',
        'callback' => '\local_student_monitor\observer::course_module_updated',
        'priority' => 0,
    ],

    // Observe when a forum discussion is created (institutional announcements).
    [
        'eventname' => '\mod_forum\event\discussion_created',
        'callback' => '\local_student_monitor\observer::forum_discussion_created',
        'priority' => 0,
    ],

    // Observe when an assignment is submitted.
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\local_student_monitor\observer::assignment_submitted',
        'priority' => 0,
    ],

    // Observe user login to update last activity.
    [
        'eventname' => '\core\event\user_loggedin',
        'callback' => '\local_student_monitor\observer::user_logged_in',
        'priority' => 0,
    ],

    // Observe course viewed to track student engagement.
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => '\local_student_monitor\observer::course_viewed',
        'priority' => 0,
    ],
];
