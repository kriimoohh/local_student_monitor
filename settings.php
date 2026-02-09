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
 * Plugin administration pages.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_student_monitor', get_string('pluginname', 'local_student_monitor'));

    // General Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/generalsettings',
        get_string('generalsettings', 'local_student_monitor'),
        get_string('generalsettingsdesc', 'local_student_monitor')
    ));

    // Enable plugin.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/enabled',
        get_string('enabled', 'local_student_monitor'),
        get_string('enabled_desc', 'local_student_monitor'),
        1
    ));

    // Institution name.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/institution_name',
        get_string('institutionname', 'local_student_monitor'),
        get_string('institutionname_desc', 'local_student_monitor'),
        'UNCHK',
        PARAM_TEXT
    ));

    // Inactivity Detection Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/inactivitysettings',
        get_string('inactivitysettings', 'local_student_monitor'),
        get_string('inactivitysettingsdesc', 'local_student_monitor')
    ));

    // Inactivity threshold level 1 (72 hours = 3 days).
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/inactivity_threshold_1',
        get_string('inactivitythreshold1', 'local_student_monitor'),
        get_string('inactivitythreshold1_desc', 'local_student_monitor'),
        3,
        PARAM_INT
    ));

    // Inactivity threshold level 2 (7 days).
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/inactivity_threshold_2',
        get_string('inactivitythreshold2', 'local_student_monitor'),
        get_string('inactivitythreshold2_desc', 'local_student_monitor'),
        7,
        PARAM_INT
    ));

    // Inactivity threshold level 3 (14 days).
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/inactivity_threshold_3',
        get_string('inactivitythreshold3', 'local_student_monitor'),
        get_string('inactivitythreshold3_desc', 'local_student_monitor'),
        14,
        PARAM_INT
    ));

    // Missing Activities Thresholds.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/missingactivitiessettings',
        get_string('missingactivitiessettings', 'local_student_monitor'),
        get_string('missingactivitiessettingsdesc', 'local_student_monitor')
    ));

    // Missing activities threshold level 1.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/missing_activities_threshold_1',
        get_string('missingactivitiesthreshold1', 'local_student_monitor'),
        get_string('missingactivitiesthreshold1_desc', 'local_student_monitor'),
        1,
        PARAM_INT
    ));

    // Missing activities threshold level 2.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/missing_activities_threshold_2',
        get_string('missingactivitiesthreshold2', 'local_student_monitor'),
        get_string('missingactivitiesthreshold2_desc', 'local_student_monitor'),
        3,
        PARAM_INT
    ));

    // Missing activities threshold level 3.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/missing_activities_threshold_3',
        get_string('missingactivitiesthreshold3', 'local_student_monitor'),
        get_string('missingactivitiesthreshold3_desc', 'local_student_monitor'),
        5,
        PARAM_INT
    ));

    // Assignment Reminder Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/assignmentremindersettings',
        get_string('assignmentremindersettings', 'local_student_monitor'),
        get_string('assignmentremindersettingsdesc', 'local_student_monitor')
    ));

    // Enable assignment reminders.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/assignment_reminders_enabled',
        get_string('assignmentreminders', 'local_student_monitor'),
        get_string('assignmentreminders_desc', 'local_student_monitor'),
        1
    ));

    // Reminder days (comma-separated: 7,3,1).
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/reminder_days',
        get_string('reminderdays', 'local_student_monitor'),
        get_string('reminderdays_desc', 'local_student_monitor'),
        '7,3,1',
        PARAM_TEXT
    ));

    // Institutional Announcement Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/institutionalannouncementsettings',
        get_string('institutionalannouncementsettings', 'local_student_monitor'),
        get_string('institutionalannouncementsettingsdesc', 'local_student_monitor')
    ));

    // Institutional forum ID.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/institutional_forum_id',
        get_string('institutionalforumid', 'local_student_monitor'),
        get_string('institutionalforumid_desc', 'local_student_monitor'),
        '',
        PARAM_INT
    ));

    // Notification Channels Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/channelsettings',
        get_string('channelsettings', 'local_student_monitor'),
        get_string('channelsettingsdesc', 'local_student_monitor')
    ));

    // Enable email notifications.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/channel_email',
        get_string('channelemail', 'local_student_monitor'),
        get_string('channelemail_desc', 'local_student_monitor'),
        1
    ));

    // Enable Moodle notifications.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/channel_moodle',
        get_string('channelmoodle', 'local_student_monitor'),
        get_string('channelmoodle_desc', 'local_student_monitor'),
        1
    ));

    // Enable SMS notifications.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/channel_sms',
        get_string('channelsms', 'local_student_monitor'),
        get_string('channelsms_desc', 'local_student_monitor'),
        0
    ));

    // SMS API URL.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/sms_api_url',
        get_string('smsapiurl', 'local_student_monitor'),
        get_string('smsapiurl_desc', 'local_student_monitor'),
        '',
        PARAM_URL
    ));

    // SMS API Key.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_student_monitor/sms_api_key',
        get_string('smsapikey', 'local_student_monitor'),
        get_string('smsapikey_desc', 'local_student_monitor'),
        ''
    ));

    // Enable WhatsApp notifications.
    $settings->add(new admin_setting_configcheckbox(
        'local_student_monitor/channel_whatsapp',
        get_string('channelwhatsapp', 'local_student_monitor'),
        get_string('channelwhatsapp_desc', 'local_student_monitor'),
        0
    ));

    // WhatsApp Phone Number ID.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/whatsapp_phone_id',
        get_string('whatsappphoneid', 'local_student_monitor'),
        get_string('whatsappphoneid_desc', 'local_student_monitor'),
        '',
        PARAM_TEXT
    ));

    // WhatsApp Access Token.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_student_monitor/whatsapp_token',
        get_string('whatsapptoken', 'local_student_monitor'),
        get_string('whatsapptoken_desc', 'local_student_monitor'),
        ''
    ));

    // Support Contact Settings.
    $settings->add(new admin_setting_heading(
        'local_student_monitor/supportsettings',
        get_string('supportsettings', 'local_student_monitor'),
        get_string('supportsettingsdesc', 'local_student_monitor')
    ));

    // Support email.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/support_email',
        get_string('supportemail', 'local_student_monitor'),
        get_string('supportemail_desc', 'local_student_monitor'),
        'support@unchk.edu.sn',
        PARAM_EMAIL
    ));

    // Support phone.
    $settings->add(new admin_setting_configtext(
        'local_student_monitor/support_phone',
        get_string('supportphone', 'local_student_monitor'),
        get_string('supportphone_desc', 'local_student_monitor'),
        '',
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);

    // Add dashboard link to Site Administration -> General section.
    $ADMIN->add(
        'root',
        new admin_externalpage(
            'local_student_monitor_dashboard',
            get_string('studentmonitordashboard', 'local_student_monitor'),
            new moodle_url('/local/student_monitor/dashboard.php'),
            'local/student_monitor:viewdashboard'
        )
    );
}
