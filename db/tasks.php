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
 * Task definitions for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    // Check for student inactivity every 6 hours.
    [
        'classname' => 'local_student_monitor\task\check_inactivity',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/6',    // Every 6 hours
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],

    // Check for upcoming assignment deadlines daily at 1 AM.
    [
        'classname' => 'local_student_monitor\task\check_assignments_due',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1',      // 1 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],

    // Send scheduled/pending notifications every 15 minutes.
    [
        'classname' => 'local_student_monitor\task\send_scheduled_notifications',
        'blocking' => 0,
        'minute' => '*/15', // Every 15 minutes
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],

    // Update student tracking data daily at 2:30 AM.
    [
        'classname' => 'local_student_monitor\task\update_student_tracking',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '2',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],

    // Generate weekly reports every Monday at 8 AM.
    [
        'classname' => 'local_student_monitor\task\generate_weekly_report',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '8',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '1'  // Monday
    ],

    // Cleanup old logs monthly on the 1st day at 3 AM.
    [
        'classname' => 'local_student_monitor\task\cleanup_old_logs',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '1',
        'month' => '*',
        'dayofweek' => '*'
    ],
];
