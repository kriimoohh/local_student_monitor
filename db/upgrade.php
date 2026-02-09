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

    return true;
}
