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
 * Library of interface functions and constants.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add Student Monitor link to navigation.
 *
 * @param global_navigation $navigation
 */
function local_student_monitor_extend_navigation(global_navigation $navigation) {
    global $PAGE, $USER;

    $context = context_system::instance();

    if (has_capability('local/student_monitor:viewdashboard', $context)) {
        $node = $navigation->add(
            get_string('pluginname', 'local_student_monitor'),
            new moodle_url('/local/student_monitor/dashboard.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_student_monitor',
            new pix_icon('i/dashboard', '')
        );
        $node->showinflatnavigation = true;
    }
}

/**
 * Add Student Monitor settings to navigation.
 *
 * @param settings_navigation $navigation
 * @param context $context
 */
function local_student_monitor_extend_settings_navigation(settings_navigation $navigation, context $context) {
    global $PAGE;

    // Add course-specific settings if in a course context.
    if ($context->contextlevel == CONTEXT_COURSE && has_capability('local/student_monitor:managesettings', $context)) {
        $settingsnode = $navigation->get('courseadmin');
        if ($settingsnode) {
            $url = new moodle_url('/local/student_monitor/course_settings.php', ['id' => $context->instanceid]);
            $node = navigation_node::create(
                get_string('studentmonitorsettings', 'local_student_monitor'),
                $url,
                navigation_node::NODETYPE_LEAF,
                'local_student_monitor',
                'local_student_monitor',
                new pix_icon('i/settings', '')
            );
            if ($settingsnode) {
                $settingsnode->add_node($node);
            }
        }
    }
}

/**
 * Serve the files from the local_student_monitor file areas.
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_student_monitor_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    require_login();

    if (!has_capability('local/student_monitor:viewdashboard', $context)) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_student_monitor/$filearea/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Get risk level CSS class.
 *
 * @param string $risklevel
 * @return string
 */
function local_student_monitor_get_risk_class($risklevel) {
    $classes = [
        'FAIBLE' => 'risk-low',
        'MOYEN' => 'risk-medium',
        'ÉLEVÉ' => 'risk-high',
        'CRITIQUE' => 'risk-critical',
    ];
    return $classes[$risklevel] ?? 'risk-unknown';
}

/**
 * Get notification type display name.
 *
 * @param string $type
 * @return string
 */
function local_student_monitor_get_notification_type_name($type) {
    $types = [
        'inactivity_level1' => get_string('inactivitylevel1', 'local_student_monitor'),
        'inactivity_level2' => get_string('inactivitylevel2', 'local_student_monitor'),
        'inactivity_level3' => get_string('inactivitylevel3', 'local_student_monitor'),
        'new_content' => get_string('newcontent', 'local_student_monitor'),
        'assignment_reminder' => get_string('assignmentreminder', 'local_student_monitor'),
        'institutional_announcement' => get_string('institutionalannouncement', 'local_student_monitor'),
        'manual_alert' => get_string('manualalert', 'local_student_monitor'),
    ];
    return $types[$type] ?? $type;
}
