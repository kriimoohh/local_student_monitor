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

    // Main Student Monitor node.
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

        // === TABLEAUX DE BORD ===
        $dashboardsnode = $node->add(
            '📊 Tableaux de bord',
            null,
            navigation_node::TYPE_CONTAINER,
            null,
            'sm_dashboards'
        );
        $dashboardsnode->showinflatnavigation = true;

        $dashboardsnode->add(
            get_string('studentmonitordashboard', 'local_student_monitor'),
            new moodle_url('/local/student_monitor/dashboard.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'sm_dashboard',
            new pix_icon('i/dashboard', '')
        );

        if (has_capability('local/student_monitor:viewreports', $context)) {
            $dashboardsnode->add(
                get_string('bidashboard', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/bi_dashboard.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_bi_dashboard',
                new pix_icon('i/report', '')
            );

            $dashboardsnode->add(
                get_string('weeklyreport', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/weekly_report.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_weekly_report',
                new pix_icon('i/report', '')
            );
        }

        // === GESTION DES ÉTUDIANTS ===
        $studentsnode = $node->add(
            '👥 Gestion des étudiants',
            null,
            navigation_node::TYPE_CONTAINER,
            null,
            'sm_students'
        );
        $studentsnode->showinflatnavigation = true;

        $studentsnode->add(
            get_string('studentsatrisk', 'local_student_monitor'),
            new moodle_url('/local/student_monitor/students_at_risk.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'sm_students_at_risk',
            new pix_icon('i/risk', '')
        );

        if (has_capability('local/student_monitor:intervene', $context)) {
            $studentsnode->add(
                get_string('bulkactions', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/bulk_actions.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_bulk_actions',
                new pix_icon('i/settings', '')
            );
        }

        // === ALERTES ET NOTIFICATIONS ===
        if (has_capability('local/student_monitor:sendmanual', $context)) {
            $alertsnode = $node->add(
                '📧 Alertes & Notifications',
                null,
                navigation_node::TYPE_CONTAINER,
                null,
                'sm_alerts'
            );
            $alertsnode->showinflatnavigation = true;

            $alertsnode->add(
                get_string('createalert', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/create_alert.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_create_alert',
                new pix_icon('i/edit', '')
            );

            $alertsnode->add(
                get_string('viewalerts', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/view_alerts.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_view_alerts',
                new pix_icon('i/report', '')
            );

            if (has_capability('local/student_monitor:managesettings', $context)) {
                $alertsnode->add(
                    get_string('configureautomaticalerts', 'local_student_monitor'),
                    new moodle_url('/local/student_monitor/configure_automatic_alerts.php'),
                    navigation_node::TYPE_CUSTOM,
                    null,
                    'sm_configure_alerts',
                    new pix_icon('i/settings', '')
                );
            }
        }

        // === RAPPORTS ET ANALYTICS ===
        if (has_capability('local/student_monitor:viewreports', $context)) {
            $reportsnode = $node->add(
                '📈 Rapports & Analytics',
                null,
                navigation_node::TYPE_CONTAINER,
                null,
                'sm_reports'
            );
            $reportsnode->showinflatnavigation = true;

            $reportsnode->add(
                get_string('advancedreports', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/reports.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_advanced_reports',
                new pix_icon('i/report', '')
            );

            $reportsnode->add(
                get_string('predictiveanalytics', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/predictions.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_predictions',
                new pix_icon('i/report', '')
            );

            $reportsnode->add(
                get_string('effectivenessreports', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/effectiveness.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_effectiveness',
                new pix_icon('i/report', '')
            );

            $reportsnode->add(
                get_string('reportschedules', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/report_schedules.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_report_schedules',
                new pix_icon('i/scheduled', '')
            );
        }

        // === CAMPAGNES EMAIL ===
        if (has_capability('local/student_monitor:sendmanual', $context)) {
            $campaignsnode = $node->add(
                '📧 Campagnes Email',
                null,
                navigation_node::TYPE_CONTAINER,
                null,
                'sm_campaigns'
            );
            $campaignsnode->showinflatnavigation = true;

            $campaignsnode->add(
                get_string('emailcampaigns', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/campaigns.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_campaigns_list',
                new pix_icon('i/email', '')
            );

            $campaignsnode->add(
                get_string('campaignstatistics', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/campaign_stats.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_campaign_stats',
                new pix_icon('i/report', '')
            );
        }

        // === COMMUNICATION ===
        if (has_capability('local/student_monitor:viewreports', $context)) {
            $commnode = $node->add(
                '💬 Communication',
                null,
                navigation_node::TYPE_CONTAINER,
                null,
                'sm_communication'
            );
            $commnode->showinflatnavigation = true;

            $commnode->add(
                get_string('communicationstats', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/communication_stats.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_comm_stats',
                new pix_icon('i/report', '')
            );

            if (has_capability('local/student_monitor:managetemplates', $context)) {
                $commnode->add(
                    get_string('templateeditor', 'local_student_monitor'),
                    new moodle_url('/local/student_monitor/template_editor.php'),
                    navigation_node::TYPE_CUSTOM,
                    null,
                    'sm_templates',
                    new pix_icon('i/edit', '')
                );
            }
        }

        // === GESTION ===
        if (has_capability('local/student_monitor:intervene', $context)) {
            $managementnode = $node->add(
                '⚙️ Gestion',
                null,
                navigation_node::TYPE_CONTAINER,
                null,
                'sm_management'
            );
            $managementnode->showinflatnavigation = true;

            $managementnode->add(
                get_string('parentmanagement', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/parent_management.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_parents',
                new pix_icon('i/users', '')
            );

            $managementnode->add(
                get_string('taskmanagement', 'local_student_monitor'),
                new moodle_url('/local/student_monitor/tasks.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'sm_tasks',
                new pix_icon('i/checked', '')
            );
        }
    }

    // === ESPACE ÉTUDIANT ===
    // Visible pour tous les utilisateurs authentifiés.
    $studentnode = $navigation->add(
        '👨‍🎓 Mon espace étudiant',
        new moodle_url('/local/student_monitor/student_dashboard.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_student_space',
        new pix_icon('i/user', '')
    );
    $studentnode->showinflatnavigation = true;

    $studentnode->add(
        get_string('studentdashboard', 'local_student_monitor'),
        new moodle_url('/local/student_monitor/student_dashboard.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_student_dashboard',
        new pix_icon('i/dashboard', '')
    );

    $studentnode->add(
        get_string('mygoals', 'local_student_monitor'),
        new moodle_url('/local/student_monitor/my_goals.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_my_goals',
        new pix_icon('i/checked', '')
    );

    $studentnode->add(
        get_string('peercomparison', 'local_student_monitor'),
        new moodle_url('/local/student_monitor/peer_comparison.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_peer_comparison',
        new pix_icon('i/report', '')
    );

    $studentnode->add(
        get_string('leaderboard', 'local_student_monitor'),
        new moodle_url('/local/student_monitor/leaderboard.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_leaderboard',
        new pix_icon('i/badge', '')
    );

    $studentnode->add(
        get_string('notificationpreferences', 'local_student_monitor'),
        new moodle_url('/local/student_monitor/preferences.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'sm_preferences',
        new pix_icon('i/settings', '')
    );
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
    return \local_student_monitor\risk_level::get_css_class($risklevel);
}

/**
 * Get risk level display name (translated).
 *
 * @param string $risklevel
 * @return string
 */
function local_student_monitor_get_risk_display_name($risklevel) {
    return \local_student_monitor\risk_level::get_display_name($risklevel);
}

/**
 * Get risk level badge class (Bootstrap).
 *
 * @param string $risklevel
 * @return string
 */
function local_student_monitor_get_risk_badge_class($risklevel) {
    return \local_student_monitor\risk_level::get_badge_class($risklevel);
}

/**
 * Get risk level icon class (Font Awesome).
 *
 * @param string $risklevel
 * @return string
 */
function local_student_monitor_get_risk_icon_class($risklevel) {
    return \local_student_monitor\risk_level::get_icon_class($risklevel);
}

/**
 * Get institution name from configuration.
 *
 * @return string Institution name
 */
function local_student_monitor_get_institution_name() {
    $name = get_config('local_student_monitor', 'institution_name');
    return $name ?: 'UNCHK';
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
