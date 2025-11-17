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
 * Privacy Subsystem implementation for local_student_monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for Student Monitor.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns metadata about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_sm_notifications',
            [
                'userid' => 'privacy:metadata:local_sm_notifications:userid',
                'message' => 'privacy:metadata:local_sm_notifications:message',
                'timecreated' => 'privacy:metadata:local_sm_notifications:timecreated',
                'timeread' => 'privacy:metadata:local_sm_notifications:timeread',
            ],
            'privacy:metadata:local_sm_notifications'
        );

        $collection->add_database_table(
            'local_sm_student_tracking',
            [
                'userid' => 'privacy:metadata:local_sm_student_tracking:userid',
                'risk_level' => 'privacy:metadata:local_sm_student_tracking:risk_level',
                'last_activity' => 'privacy:metadata:local_sm_student_tracking:last_activity',
                'notes' => 'privacy:metadata:local_sm_student_tracking:notes',
            ],
            'privacy:metadata:local_sm_student_tracking'
        );

        $collection->add_database_table(
            'local_sm_logs',
            [
                'userid' => 'privacy:metadata:local_sm_logs:userid',
                'action' => 'privacy:metadata:local_sm_logs:action',
                'details' => 'privacy:metadata:local_sm_logs:details',
            ],
            'privacy:metadata:local_sm_logs'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Student Monitor data is stored in system context.
        $contextlist->add_system_context();

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        // Get users from notifications table.
        $sql = "SELECT userid FROM {local_sm_notifications}";
        $userlist->add_from_sql('userid', $sql, []);

        // Get users from tracking table.
        $sql = "SELECT userid FROM {local_sm_student_tracking}";
        $userlist->add_from_sql('userid', $sql, []);

        // Get users from logs table.
        $sql = "SELECT userid FROM {local_sm_logs} WHERE userid IS NOT NULL";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            // Export notifications.
            $notifications = $DB->get_records('local_sm_notifications', ['userid' => $userid]);
            if ($notifications) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:notifications', 'local_student_monitor')],
                    (object) ['notifications' => array_values($notifications)]
                );
            }

            // Export tracking data.
            $tracking = $DB->get_records('local_sm_student_tracking', ['userid' => $userid]);
            if ($tracking) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:tracking', 'local_student_monitor')],
                    (object) ['tracking' => array_values($tracking)]
                );
            }

            // Export logs.
            $logs = $DB->get_records('local_sm_logs', ['userid' => $userid]);
            if ($logs) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:logs', 'local_student_monitor')],
                    (object) ['logs' => array_values($logs)]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $DB->delete_records('local_sm_notifications', []);
        $DB->delete_records('local_sm_student_tracking', []);
        $DB->delete_records('local_sm_logs', []);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            $DB->delete_records('local_sm_notifications', ['userid' => $userid]);
            $DB->delete_records('local_sm_student_tracking', ['userid' => $userid]);
            $DB->delete_records('local_sm_logs', ['userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();

        foreach ($userids as $userid) {
            $DB->delete_records('local_sm_notifications', ['userid' => $userid]);
            $DB->delete_records('local_sm_student_tracking', ['userid' => $userid]);
            $DB->delete_records('local_sm_logs', ['userid' => $userid]);
        }
    }
}
