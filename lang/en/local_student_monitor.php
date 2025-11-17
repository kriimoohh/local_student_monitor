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
 * English language strings for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Student Monitor';

// Capabilities.
$string['student_monitor:viewdashboard'] = 'View Student Monitor dashboard';
$string['student_monitor:managesettings'] = 'Manage Student Monitor settings';
$string['student_monitor:sendmanual'] = 'Send manual alerts';
$string['student_monitor:viewreports'] = 'View reports and statistics';
$string['student_monitor:viewstudentdata'] = 'View student data';
$string['student_monitor:intervene'] = 'Intervene with students';
$string['student_monitor:exportdata'] = 'Export data';
$string['student_monitor:managetemplates'] = 'Manage message templates';

// General settings.
$string['generalsettings'] = 'General settings';
$string['generalsettingsdesc'] = 'General configuration for Student Monitor plugin';
$string['enabled'] = 'Enable Student Monitor';
$string['enabled_desc'] = 'Enable or disable the Student Monitor plugin';

// Inactivity settings.
$string['inactivitysettings'] = 'Inactivity detection settings';
$string['inactivitysettingsdesc'] = 'Configuration for inactivity detection thresholds';
$string['inactivitythreshold1'] = 'Level 1 threshold (days)';
$string['inactivitythreshold1_desc'] = 'Number of days of inactivity to trigger a level 1 alert (default: 3 days)';
$string['inactivitythreshold2'] = 'Level 2 threshold (days)';
$string['inactivitythreshold2_desc'] = 'Number of days of inactivity to trigger a level 2 alert (default: 7 days)';
$string['inactivitythreshold3'] = 'Level 3 threshold (days)';
$string['inactivitythreshold3_desc'] = 'Number of days of inactivity to trigger a level 3 alert (default: 14 days)';

// Assignment reminder settings.
$string['assignmentremindersettings'] = 'Assignment reminder settings';
$string['assignmentremindersettingsdesc'] = 'Configuration for automatic assignment reminders';
$string['assignmentreminders'] = 'Enable assignment reminders';
$string['assignmentreminders_desc'] = 'Send automatic reminders before assignment due dates';
$string['reminderdays'] = 'Reminder days';
$string['reminderdays_desc'] = 'List of days before due date to send reminders (comma-separated, e.g., 7,3,1)';

// Institutional announcement settings.
$string['institutionalannouncementsettings'] = 'Institutional announcement settings';
$string['institutionalannouncementsettingsdesc'] = 'Configuration for institutional announcements';
$string['institutionalforumid'] = 'Institutional forum ID';
$string['institutionalforumid_desc'] = 'Moodle forum ID used for institutional announcements';

// Channel settings.
$string['channelsettings'] = 'Notification channels';
$string['channelsettingsdesc'] = 'Configuration for different notification channels';
$string['channelemail'] = 'Enable email';
$string['channelemail_desc'] = 'Send notifications via email';
$string['channelmoodle'] = 'Enable Moodle notifications';
$string['channelmoodle_desc'] = 'Send notifications via Moodle notification system';
$string['channelsms'] = 'Enable SMS';
$string['channelsms_desc'] = 'Send notifications via SMS (requires API configuration)';
$string['smsapiurl'] = 'SMS API URL';
$string['smsapiurl_desc'] = 'API URL for sending SMS';
$string['smsapikey'] = 'SMS API Key';
$string['smsapikey_desc'] = 'Authentication key for SMS API';
$string['channelwhatsapp'] = 'Enable WhatsApp';
$string['channelwhatsapp_desc'] = 'Send notifications via WhatsApp Business API';
$string['whatsappphoneid'] = 'WhatsApp phone ID';
$string['whatsappphoneid_desc'] = 'WhatsApp Business phone number ID';
$string['whatsapptoken'] = 'WhatsApp access token';
$string['whatsapptoken_desc'] = 'Access token for WhatsApp Business API';

// Support settings.
$string['supportsettings'] = 'Support contact';
$string['supportsettingsdesc'] = 'Support contact information';
$string['supportemail'] = 'Support email';
$string['supportemail_desc'] = 'Email address for student support';
$string['supportphone'] = 'Support phone';
$string['supportphone_desc'] = 'Phone number for student support';

// Dashboard.
$string['dashboard'] = 'Dashboard';
$string['studentmonitordashboard'] = 'Student Monitor Dashboard';
$string['studentmonitorsettings'] = 'Student Monitor Settings';
$string['statistics'] = 'Statistics';
$string['studentsatrisk'] = 'Students at risk';
$string['notificationssent'] = 'Notifications sent';
$string['interventionsneeded'] = 'Interventions needed';
$string['readrate'] = 'Read rate';
$string['criticalalerts'] = 'Critical alerts';
$string['studentlist'] = 'Student list';
$string['quickactions'] = 'Quick actions';

// Risk levels.
$string['risklevel'] = 'Risk level';
$string['risk_faible'] = 'LOW';
$string['risk_moyen'] = 'MEDIUM';
$string['risk_eleve'] = 'HIGH';
$string['risk_critique'] = 'CRITICAL';

// Notification types.
$string['notificationtype'] = 'Notification type';
$string['inactivitylevel1'] = 'Inactivity level 1';
$string['inactivitylevel2'] = 'Inactivity level 2';
$string['inactivitylevel3'] = 'Inactivity level 3';
$string['newcontent'] = 'New content';
$string['assignmentreminder'] = 'Assignment reminder';
$string['institutionalannouncement'] = 'Institutional announcement';
$string['manualalert'] = 'Manual alert';

// Notification status.
$string['status'] = 'Status';
$string['status_pending'] = 'Pending';
$string['status_sent'] = 'Sent';
$string['status_delivered'] = 'Delivered';
$string['status_read'] = 'Read';
$string['status_failed'] = 'Failed';

// Manual alerts.
$string['createalert'] = 'Create alert';
$string['alerttype'] = 'Alert type';
$string['alert_exam'] = 'Exam';
$string['alert_assignment'] = 'Assignment';
$string['alert_announcement'] = 'Announcement';
$string['alert_event'] = 'Event';
$string['title'] = 'Title';
$string['eventdate'] = 'Event date';
$string['description'] = 'Description';
$string['channels'] = 'Channels';
$string['recipients'] = 'Recipients';
$string['recipients_all_course'] = 'All course';
$string['recipients_group'] = 'Specific group';
$string['recipients_manual'] = 'Manual selection';
$string['reminder7days'] = 'D-7 reminder';
$string['reminder3days'] = 'D-3 reminder';
$string['reminder1day'] = 'D-1 reminder';
$string['sendalert'] = 'Send alert';

// Student tracking.
$string['studentname'] = 'Student name';
$string['lastactivity'] = 'Last activity';
$string['inactivitydays'] = 'Inactivity days';
$string['missingassignments'] = 'Missing assignments';
$string['notificationcount'] = 'Notification count';
$string['interventionneeded'] = 'Intervention needed';
$string['assignedto'] = 'Assigned to';
$string['notes'] = 'Notes';
$string['actions'] = 'Actions';

// Reports.
$string['weeklyreport'] = 'Weekly report';
$string['exportcsv'] = 'Export CSV';
$string['exportpdf'] = 'Export PDF';

// Tasks.
$string['task_check_inactivity'] = 'Check student inactivity';
$string['task_check_assignments_due'] = 'Check assignments due';
$string['task_send_scheduled_notifications'] = 'Send scheduled notifications';
$string['task_update_student_tracking'] = 'Update student tracking';
$string['task_generate_weekly_report'] = 'Generate weekly report';
$string['task_cleanup_old_logs'] = 'Cleanup old logs';

// Messages.
$string['alertcreated'] = 'Alert created successfully';
$string['alertsent'] = 'Alert sent successfully';
$string['notificationsent'] = 'Notification sent';
$string['dataexported'] = 'Data exported successfully';
$string['settingssaved'] = 'Settings saved';

// Errors.
$string['error_sending_notification'] = 'Error sending notification';
$string['error_creating_alert'] = 'Error creating alert';
$string['error_exporting_data'] = 'Error exporting data';
$string['nopermission'] = 'You do not have permission to access this page';

// Privacy.
$string['privacy:metadata:local_sm_notifications'] = 'Information about notifications sent to users';
$string['privacy:metadata:local_sm_notifications:userid'] = 'User ID of the recipient';
$string['privacy:metadata:local_sm_notifications:message'] = 'Notification content';
$string['privacy:metadata:local_sm_notifications:timecreated'] = 'Date notification was created';
$string['privacy:metadata:local_sm_notifications:timeread'] = 'Date notification was read';

$string['privacy:metadata:local_sm_student_tracking'] = 'Student tracking data';
$string['privacy:metadata:local_sm_student_tracking:userid'] = 'Student user ID';
$string['privacy:metadata:local_sm_student_tracking:risk_level'] = 'Calculated risk level';
$string['privacy:metadata:local_sm_student_tracking:last_activity'] = 'Last activity date';
$string['privacy:metadata:local_sm_student_tracking:notes'] = 'Supervisor notes';

$string['privacy:metadata:local_sm_logs'] = 'Logs of actions performed';
$string['privacy:metadata:local_sm_logs:userid'] = 'User ID';
$string['privacy:metadata:local_sm_logs:action'] = 'Action performed';
$string['privacy:metadata:local_sm_logs:details'] = 'Action details';

// Events.
$string['event_notification_sent'] = 'Notification sent';
$string['event_alert_created'] = 'Alert created';

// Privacy export data.
$string['privacy:notifications'] = 'Notifications';
$string['privacy:tracking'] = 'Student tracking';
$string['privacy:logs'] = 'Activity logs';

// Additional strings.
$string['nostudents'] = 'No students found';
$string['all'] = 'All';
$string['filter'] = 'Filter';
$string['allstudents'] = 'All students';
$string['location'] = 'Location';
$string['reminders'] = 'Automatic reminders';
$string['reminders_help'] = 'Create automatic reminders before the event';
$string['eventdate_help'] = 'Date and time of the event or deadline';
$string['selectusers'] = 'Please select at least one user';
$string['selectatleastonechannel'] = 'Please select at least one communication channel';
$string['createalertdesc'] = 'Create a manual alert to inform students about an exam, assignment, or important event.';
$string['viewalerts'] = 'Alert history';
$string['recentalerts'] = 'Recent alerts';
$string['noalerts'] = 'No alerts found';
$string['sentby'] = 'Sent by';
$string['timecreated'] = 'Created on';
$string['back'] = 'Back';
$string['view'] = 'View';
$string['choosedots'] = 'Choose...';

// Course settings.
$string['coursesettings'] = 'Course settings';
$string['coursesettingsdesc'] = 'Configure Student Monitor for this specific course';
$string['generalsection'] = 'General settings';
$string['newcontentnotifications'] = 'New content notifications';
$string['notifynewcontent'] = 'Notify new pedagogical content';
$string['notifynewcontent_help'] = 'Send notifications when new content is added to the course';
$string['activitytypes'] = 'Activity types to monitor';
$string['activity_assign'] = 'Assignments';
$string['activity_quiz'] = 'Quizzes';
$string['activity_forum'] = 'Forums';
$string['activity_resource'] = 'Resources';
$string['activity_url'] = 'URLs';
$string['activity_page'] = 'Pages';
$string['assignmentreminderssection'] = 'Assignment reminders';
$string['reminderdays_custom'] = 'Custom reminder days';
$string['reminderdays_custom_help'] = 'List of days before due date (e.g., 7,3,1)';
$string['inactivitymonitoringsection'] = 'Inactivity monitoring';
$string['monitorinactivity'] = 'Monitor inactivity';
$string['monitorinactivity_help'] = 'Enable inactivity detection for this course';
$string['inactivitythreshold_custom'] = 'Custom inactivity threshold (days)';
$string['inactivitythreshold_custom_help'] = 'Number of days of inactivity before alert';
$string['supervisorssection'] = 'Supervisors';
$string['defaultsupervisor'] = 'Default supervisor';
$string['defaultsupervisor_help'] = 'Supervisor automatically assigned to at-risk students';
$string['notificationpreferencessection'] = 'Notification preferences';
$string['teacherdigest'] = 'Teacher digest';
$string['teacherdigest_help'] = 'Send periodic summary to teachers';
$string['digestfrequency'] = 'Digest frequency';
$string['digestfrequency_help'] = 'How often to send the digest';
$string['digest_daily'] = 'Daily';
$string['digest_weekly'] = 'Weekly';
$string['digest_monthly'] = 'Monthly';

// Student preferences.
$string['notificationpreferences'] = 'Notification preferences';
$string['notificationpreferencesdesc'] = 'Manage how you want to receive Student Monitor notifications';
$string['channelpreferences'] = 'Reception channels';
$string['channelpreferences_help'] = 'Select the channels through which you want to receive notifications';
$string['receivevia'] = 'Receive via';
$string['channel_email'] = 'Email';
$string['channel_moodle'] = 'Moodle notification';
$string['channel_sms'] = 'SMS';
$string['channel_whatsapp'] = 'WhatsApp';
$string['notificationhistory'] = 'Notification history';
$string['nonotifications'] = 'No notifications found';
$string['subject'] = 'Subject';
$string['timesent'] = 'Sent on';
$string['preferencessaved'] = 'Preferences saved successfully';
$string['recipients_all_students'] = 'All students';
