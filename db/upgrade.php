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
 * Database upgrade script for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_student_monitor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026020701) {
        $table = new xmldb_table('local_sm_student_tracking');

        // Rename 'missing_assignments' to 'missing_activities'.
        $field = new xmldb_field('missing_assignments', XMLDB_TYPE_INTEGER, '5', null, null, null, '0', 'inactivity_days');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'missing_activities');
        }

        // Migrate risk_level values from French to English.
        $DB->execute("UPDATE {local_sm_student_tracking} SET risk_level = 'LOW' WHERE risk_level = 'FAIBLE'");
        $DB->execute("UPDATE {local_sm_student_tracking} SET risk_level = 'MEDIUM' WHERE risk_level = 'MOYEN'");
        $DB->execute("UPDATE {local_sm_student_tracking} SET risk_level = 'HIGH' WHERE risk_level IN ('ÉLEVÉ', 'ELEVE')");
        $DB->execute("UPDATE {local_sm_student_tracking} SET risk_level = 'CRITICAL' WHERE risk_level = 'CRITIQUE'");

        // Clean up obsolete config keys.
        unset_config('threshold_critical', 'local_student_monitor');
        unset_config('threshold_high', 'local_student_monitor');
        unset_config('threshold_medium', 'local_student_monitor');

        upgrade_plugin_savepoint(true, 2026020701, 'local', 'student_monitor');
    }

    if ($oldversion < 2026020707) {
        // Create missing tables for features added in v1.3.0 - v1.9.0.

        // Table: local_sm_report_schedules.
        $table = new xmldb_table('local_sm_report_schedules');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('report_type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
            $table->add_field('frequency', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
            $table->add_field('recipients', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('parameters', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('format', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'pdf');
            $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('last_run', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('next_run', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('idx_enabled', XMLDB_INDEX_NOTUNIQUE, ['enabled']);
            $table->add_index('idx_next_run', XMLDB_INDEX_NOTUNIQUE, ['next_run']);
            $dbman->create_table($table);
        }

        // Table: local_sm_campaigns.
        $table = new xmldb_table('local_sm_campaigns');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('campaign_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('subject', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('target_criteria', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('scheduled_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'draft');
            $table->add_field('ab_testing', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('recipients_count', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('sent_count', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('failed_count', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('created_by', XMLDB_KEY_FOREIGN, ['created_by'], 'user', ['id']);
            $table->add_index('idx_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $dbman->create_table($table);
        }

        // Table: local_sm_campaign_recipients.
        $table = new xmldb_table('local_sm_campaign_recipients');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('campaign_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('variant', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('sent_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('opened_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('clicked_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('converted_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('campaign_id', XMLDB_KEY_FOREIGN, ['campaign_id'], 'local_sm_campaigns', ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
            $table->add_index('idx_campaign_userid', XMLDB_INDEX_NOTUNIQUE, ['campaign_id', 'userid']);
            $dbman->create_table($table);
        }

        // Table: local_sm_parents.
        $table = new xmldb_table('local_sm_parents');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('parent_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('parent_email', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('parent_phone', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('relationship', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'parent');
            $table->add_field('notify_enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('notify_frequency', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'critical');
            $table->add_field('language', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, 'fr');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('student_id', XMLDB_KEY_FOREIGN, ['student_id'], 'user', ['id']);
            $dbman->create_table($table);
        }

        // Table: local_sm_tasks.
        $table = new xmldb_table('local_sm_tasks');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('supervisor_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('task_type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
            $table->add_field('priority', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'normal');
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('due_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('completed_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('completed_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('completion_notes', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('supervisor_id', XMLDB_KEY_FOREIGN, ['supervisor_id'], 'user', ['id']);
            $table->add_key('student_id', XMLDB_KEY_FOREIGN, ['student_id'], 'user', ['id']);
            $table->add_index('idx_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $dbman->create_table($table);
        }

        // Table: local_sm_achievements.
        $table = new xmldb_table('local_sm_achievements');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('achievement_key', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('achievement_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('badge', XMLDB_TYPE_CHAR, '50', null, null, null, null);
            $table->add_field('points_awarded', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
            $table->add_index('idx_userid_key', XMLDB_INDEX_NOTUNIQUE, ['userid', 'achievement_key']);
            $dbman->create_table($table);
        }

        // Table: local_sm_gamification.
        $table = new xmldb_table('local_sm_gamification');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('total_points', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('level', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('current_streak', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('longest_streak', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('last_activity', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN_UNIQUE, ['userid'], 'user', ['id']);
            $dbman->create_table($table);
        }

        // Table: local_sm_interventions.
        $table = new xmldb_table('local_sm_interventions');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('supervisor_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('intervention_type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
            $table->add_field('notes', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('metadata', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('student_id', XMLDB_KEY_FOREIGN, ['student_id'], 'user', ['id']);
            $table->add_key('supervisor_id', XMLDB_KEY_FOREIGN, ['supervisor_id'], 'user', ['id']);
            $table->add_index('idx_timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $dbman->create_table($table);
        }

        // Table: local_sm_sms_costs.
        $table = new xmldb_table('local_sm_sms_costs');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('notification_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('phone_number', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('message_length', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('message_parts', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('cost_per_sms', XMLDB_TYPE_NUMBER, '10, 4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('total_cost', XMLDB_TYPE_NUMBER, '10, 4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('currency', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, 'XOF');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('notification_id', XMLDB_KEY_FOREIGN, ['notification_id'], 'local_sm_notifications', ['id']);
            $table->add_index('idx_timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $dbman->create_table($table);
        }

        // Table: local_sm_goals.
        $table = new xmldb_table('local_sm_goals');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
            $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('target_value', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('current_value', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('deadline', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active');
            $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
            $table->add_index('idx_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $dbman->create_table($table);
        }

        // Table: local_sm_custom_reports.
        $table = new xmldb_table('local_sm_custom_reports');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('report_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('columns', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('filters', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('sorting', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('is_public', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('user_id', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026020707, 'local', 'student_monitor');
    }

    return true;
}
