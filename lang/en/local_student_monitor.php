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

// Advanced reports (Phase 4).
$string['advancedreports'] = 'Advanced reports';
$string['riskdistribution'] = 'Risk distribution';
$string['notificationtrends'] = 'Notification trends';
$string['notificationtypes'] = 'Notification types';
$string['interventionsbyrisk'] = 'Interventions by risk';
$string['totalstudents'] = 'Total students';
$string['totalnotifications'] = 'Total notifications';
$string['last30days'] = 'Last 30 days';
$string['criticalandhigh'] = 'Critical and High';
$string['notificationsread'] = 'Notifications read';
$string['notifications'] = 'Notifications';
$string['interventions'] = 'Interventions';
$string['backtodashboard'] = 'Back to dashboard';
$string['exportstudents'] = 'Export students';
$string['exportnotifications'] = 'Export notifications';

// Bulk actions (Phase 4).
$string['bulkactions'] = 'Bulk actions';
$string['bulkactionsdesc'] = 'Perform actions on multiple students simultaneously';
$string['selectaction'] = 'Select action';
$string['bulkaction_assign'] = 'Assign to supervisor';
$string['bulkaction_unassign'] = 'Remove assignment';
$string['bulkaction_addnote'] = 'Add note';
$string['bulkaction_notify'] = 'Send notification';
$string['selectsupervisor'] = 'Select supervisor';
$string['noteormessage'] = 'Note or message';
$string['selectstudents'] = 'Select students';
$string['executeaction'] = 'Execute action';
$string['confirmaction'] = 'Confirm action';
$string['confirmactionmsg'] = 'You are about to execute "{action}" for {count} student(s). Continue?';
$string['bulkactionsuccess'] = '{success} action(s) successful, {failed} failed';
$string['bulknotificationsubject'] = 'Bulk message - Student Monitor';
$string['bulknotificationmessage'] = 'This message was sent to you by your academic supervisor via Student Monitor.';

// Template editor (Phase 4).
$string['templateeditor'] = 'Template editor';
$string['templateeditordesc'] = 'Customize notification templates sent to students';
$string['edittemplate'] = 'Edit template';
$string['body'] = 'Message body';
$string['availableplaceholders'] = 'Available placeholders';
$string['placeholdersdesc'] = 'You can use these placeholders in the subject and body. They will be automatically replaced.';
$string['templatesaved'] = 'Template saved successfully';
$string['templatedeleted'] = 'Template deleted';
$string['templateresettodefault'] = 'Template reset to default values';
$string['resettodefault'] = 'Reset to default';
$string['notemplates'] = 'No templates found';
$string['templatetype'] = 'Template type';
$string['lastmodified'] = 'Last modified';

// Advanced filters (Phase 4).
$string['advancedfilters'] = 'Advanced filters';
$string['searchstudents'] = 'Search students';
$string['searchplaceholder'] = 'Name or email...';
$string['filterbyinactivity'] = 'Filter by inactivity';
$string['filterbymissingassignments'] = 'Filter by missing assignments';
$string['filterbyassigned'] = 'Filter by assignment';
$string['assigned'] = 'Assigned';
$string['unassigned'] = 'Unassigned';
$string['clearfilters'] = 'Clear filters';
$string['visiblestudents'] = 'Visible students';
$string['selectallvisible'] = 'Select all (visible)';
$string['email'] = 'Email';

// PDF Export & Communication (Phase 5).
$string['studentreport'] = 'Student Report';
$string['studentmonitorreport'] = 'Student Monitor Report';
$string['generatedon'] = 'Generated on';
$string['summary'] = 'Summary';
$string['detailedreport'] = 'Detailed Report';
$string['studentmonitordetailedreport'] = 'Student Monitor Detailed Report';
$string['overview'] = 'Overview';
$string['generatedby'] = 'Generated by';
$string['total'] = 'Total';
$string['student'] = 'Student';

// Communication statistics (Phase 5).
$string['communicationstats'] = 'Communication Statistics';
$string['period'] = 'Period';
$string['thisweek'] = 'This week';
$string['thismonth'] = 'This month';
$string['thisyear'] = 'This year';
$string['totalsmssent'] = 'Total SMS sent';
$string['parts'] = 'parts';
$string['totalcost'] = 'Total cost';
$string['currentperiod'] = 'Current period';
$string['avgcostpersms'] = 'Average cost per SMS';
$string['monthlybudget'] = 'Monthly budget';
$string['dailysmscosts'] = 'Daily SMS costs';
$string['costbytype'] = 'Cost by type';
$string['channeldistribution'] = 'Channel distribution';
$string['channel'] = 'Channel';
$string['count'] = 'Count';
$string['nodata'] = 'No data available';

// PDF export actions.
$string['exportstudentspdf'] = 'Export students (PDF)';
$string['exportnotificationspdf'] = 'Export notifications (PDF)';
$string['exportdetailedpdf'] = 'Export detailed report (PDF)';
$string['invalidexporttype'] = 'Invalid export type';

// Workflow automation & Tasks (Phase 6).
$string['taskmanagement'] = 'Task Management';
$string['taskcompleted'] = 'Task marked as completed';
$string['taskdeferred'] = 'Task deferred';
$string['taskreassigned'] = 'Task reassigned';
$string['notasksfound'] = 'No tasks found';
$string['tasktype'] = 'Task type';
$string['duedate'] = 'Due date';
$string['actions'] = 'Actions';
$string['totaltasks'] = 'Total tasks';
$string['pendingtasks'] = 'Pending tasks';
$string['inprogresstasks'] = 'In progress tasks';
$string['overduetasks'] = 'Overdue tasks';
$string['filterbystatus'] = 'Filter by status';
$string['all'] = 'All';
$string['pending'] = 'Pending';
$string['inprogress'] = 'In progress';
$string['completed'] = 'Completed';
$string['overdue'] = 'Overdue';
$string['startwork'] = 'Start work';
$string['markcomplete'] = 'Mark as complete';
$string['viewdetails'] = 'View details';

// Task types.
$string['tasktype_urgent_intervention'] = 'Urgent intervention';
$string['tasktype_follow_up'] = 'Follow-up';
$string['tasktype_preventive'] = 'Preventive';
$string['tasktype_check_in'] = 'Check-in';

// Task priorities.
$string['priority'] = 'Priority';
$string['priority_urgent'] = 'Urgent';
$string['priority_high'] = 'High';
$string['priority_normal'] = 'Normal';
$string['priority_low'] = 'Low';

// Task statuses.
$string['status'] = 'Status';
$string['status_pending'] = 'Pending';
$string['status_in_progress'] = 'In progress';
$string['status_completed'] = 'Completed';

// Intervention tracking.
$string['interventionlogged'] = 'Intervention logged';
$string['interventiontype'] = 'Intervention type';
$string['interventionnotes'] = 'Intervention notes';
$string['interventionhistory'] = 'Intervention history';
$string['lastintervention'] = 'Last intervention';
$string['interventioncount'] = 'Intervention count';
$string['task_completed'] = 'Task completed';
$string['phone_call'] = 'Phone call';
$string['meeting'] = 'Meeting';
$string['email_response'] = 'Email response';

// Business rules.
$string['businessrules'] = 'Business rules';
$string['rulename'] = 'Rule name';
$string['ruleconditions'] = 'Conditions';
$string['ruleactions'] = 'Actions';
$string['ruleenabled'] = 'Rule enabled';
$string['ruledisabled'] = 'Rule disabled';
$string['ruleexecuted'] = 'Rule executed';
$string['createrule'] = 'Create rule';
$string['testrule'] = 'Test rule';

// Effectiveness reports.
$string['effectivenessreports'] = 'Effectiveness Reports';
$string['overalleffectiveness'] = 'Overall effectiveness';
$string['studentsimproved'] = 'Students improved';
$string['successrate'] = 'Success rate';
$string['avginterventions'] = 'Average interventions';
$string['perstudent'] = 'per student';
$string['supervisorperformance'] = 'Supervisor performance';
$string['taskscompleted'] = 'Tasks completed';
$string['taskspending'] = 'Tasks pending';
$string['tasksoverdue'] = 'Tasks overdue';
$string['avgresponsetime'] = 'Average response time';
$string['hours'] = 'hours';
$string['risktransitions'] = 'Risk transitions';
$string['interventiontypes'] = 'Intervention types';
$string['improved'] = 'Improved';
$string['stable'] = 'Stable';
$string['deteriorated'] = 'Deteriorated';
$string['thisquarter'] = 'This quarter';
$string['allsupervisors'] = 'All supervisors';

// Workflow messages.
$string['urgentintervention'] = 'Urgent intervention required';
$string['criticalriskmessage'] = 'Your academic risk level is critical. Please contact your supervisor immediately.';
$string['followupreminder'] = 'Follow-up reminder';
$string['highriskmessage'] = 'Your academic activity requires attention. Please reconnect to your course.';
$string['preventivereminder'] = 'Preventive reminder';
$string['mediumriskmessage'] = 'We have noticed a decrease in your activity. Please feel free to contact us if you need help.';
$string['escalationsubject'] = 'Escalation - Student in critical situation';
$string['escalationmessage'] = 'Student {$a->studentname} is in critical situation (Level: {$a->risklevel}). Inactivity: {$a->inactivity} days, Missing assignments: {$a->missing}.';
$string['automatednotification'] = 'Automated notification';
$string['risknotificationmessage'] = 'Automated alert: Risk level {$a->risklevel}, Inactivity: {$a->inactivity} days.';
$string['supervisornotification'] = 'Supervisor notification';
$string['studentneedsattention'] = 'Student {$a->studentname} needs your attention (Level: {$a->risklevel}).';
$string['systemalert'] = 'Student Monitor system alert';
$string['taskreassignedsubject'] = 'New task assigned';
$string['taskreassignedmessage'] = 'A task of type {$a->tasktype} has been assigned to you. Due date: {$a->duedate}.';

// Supervisor settings.
$string['supervisor'] = 'Supervisor';
$string['defaultsupervisor'] = 'Default supervisor';
$string['assignsupervisor'] = 'Assign supervisor';
$string['coordinatoremail'] = 'Coordinator email';
$string['coordinatoremail_desc'] = 'Academic coordinator email for escalations';

// Type labels.
$string['type'] = 'Type';

// Predictive analytics (Phase 7).
$string['predictiveanalytics'] = 'Predictive Analytics';
$string['predictionhorizon'] = 'Prediction horizon';
$string['totalpredictions'] = 'Total predictions';
$string['earlywarnings'] = 'Early warnings';
$string['atriskpredicted'] = 'at risk predicted';
$string['avgconfidence'] = 'Average confidence';
$string['deterioratingtrend'] = 'Deteriorating trend';
$string['ofstudents'] = 'of students';
$string['predictedriskdistribution'] = 'Predicted risk distribution';
$string['trenddirection'] = 'Trend direction';
$string['currentrisk'] = 'Current risk';
$string['predictedrisk'] = 'Predicted risk';
$string['confidence'] = 'Confidence';
$string['probability'] = 'Probability';
$string['trend'] = 'Trend';
$string['keyfactors'] = 'Key factors';
$string['noearlywarnings'] = 'No early warnings detected';
$string['predictiondetails'] = 'Prediction details';
$string['predictionhorizoninfo'] = 'Predictions for the next {$a} days';
$string['predictiondateinfo'] = 'Prediction date: {$a}';
$string['predictionmethodinfo'] = 'Method: Linear regression on historical data';
$string['predictionconfidenceinfo'] = 'Confidence based on data quality and quantity';
$string['days'] = 'days';
$string['daysago'] = 'days ago';

// Parent/Guardian management (Phase 7).
$string['parentmanagement'] = 'Parent/Guardian Management';
$string['registeredparents'] = 'Registered parents';
$string['notificationsthismonth'] = 'Notifications this month';
$string['uniqueparentsnotified'] = 'Unique parents notified';
$string['addparent'] = 'Add parent/guardian';
$string['parentname'] = 'Parent name';
$string['parentemail'] = 'Parent email';
$string['parentphone'] = 'Parent phone';
$string['relationship'] = 'Relationship';
$string['parent'] = 'Parent';
$string['guardian'] = 'Guardian';
$string['tutor'] = 'Academic tutor';
$string['selectstudent'] = 'Select a student';
$string['parentadded'] = 'Parent/guardian added successfully';
$string['parentdeleted'] = 'Parent/guardian deleted';
$string['parentsnotified'] = 'parents/guardians notified';
$string['studentswitparents'] = 'Students with registered parents';
$string['noparentsregistered'] = 'No parents/guardians registered';
$string['notifyparents'] = 'Notify parents';
$string['parentnotificationsubject'] = 'Important information about your child';
$string['parentnotificationtemplate'] = 'Hello {$a->parentname},\n\nWe are contacting you regarding {$a->studentname}.\n\nRisk level: {$a->risklevel}\nInactivity days: {$a->inactivitydays}\nMissing assignments: {$a->missingassignments}\n\nSupport contact: {$a->supportemail} / {$a->supportphone}';
$string['recommendations'] = 'Recommendations';
$string['recommendcontactstudent'] = 'Contact your child to understand the situation';
$string['recommendassignmenthelp'] = 'Help with overdue assignments';
$string['recommendurgencontact'] = 'Contact your child immediately';
$string['recommendcontactsupervisor'] = 'Contact the academic supervisor';
$string['recommendencouragement'] = 'Encourage perseverance';
$string['parentsmstemplate'] = 'ALERT: {$a->studentname} - Level: {$a->risklevel}. Contact support.';
$string['weeklydigestsubject'] = 'Weekly summary';
$string['weeklydigestintro'] = 'Hello {$a->parentname},\n\nHere is the weekly summary for {$a->studentname}:';
$string['weeklyactivitysummary'] = 'Activity summary';
$string['lastlogin'] = 'Last login';

// Custom report builder (Phase 7).
$string['customreportbuilder'] = 'Custom Report Builder';
$string['createcustomreport'] = 'Create custom report';
$string['savedreports'] = 'Saved reports';
$string['selectcolumns'] = 'Select columns';
$string['selectfilters'] = 'Select filters';
$string['reportname'] = 'Report name';
$string['savereport'] = 'Save report';
$string['runreport'] = 'Run report';
$string['deletereport'] = 'Delete report';
$string['exportreport'] = 'Export report';
$string['reportstatistics'] = 'Report statistics';
$string['column_student_name'] = 'Student name';
$string['column_student_email'] = 'Student email';
$string['column_risk_level'] = 'Risk level';
$string['column_inactivity_days'] = 'Inactivity days';
$string['column_missing_assignments'] = 'Missing assignments';
$string['column_notification_count'] = 'Notifications sent';
$string['column_last_login'] = 'Last login';
$string['column_assigned_to'] = 'Assigned supervisor';
$string['column_intervention_count'] = 'Interventions';
$string['column_last_intervention'] = 'Last intervention';
$string['column_grade_average'] = 'Grade average';
$string['column_course_count'] = 'Enrolled courses';
$string['column_predicted_risk'] = 'Predicted risk';

// Email campaigns (Phase 8).
$string['emailcampaigns'] = 'Email campaigns';
$string['createnewcampaign'] = 'Create new campaign';
$string['campaignname'] = 'Campaign name';
$string['subject'] = 'Subject';
$string['message'] = 'Message';
$string['targetaudience'] = 'Target audience';
$string['scheduledtime'] = 'Scheduled time';
$string['abtesting'] = 'A/B testing';
$string['recipients'] = 'Recipients';
$string['campaignsent'] = 'Campaign sent: {$a->sent} successful, {$a->failed} failed';
$string['campaigndeleted'] = 'Campaign deleted';
$string['totalcampaigns'] = 'Total campaigns';
$string['campaignssent'] = 'Campaigns sent';
$string['drafts'] = 'Drafts';
$string['scheduled'] = 'Scheduled';
$string['status_draft'] = 'Draft';
$string['status_scheduled'] = 'Scheduled';
$string['status_sending'] = 'Sending';
$string['status_sent'] = 'Sent';
$string['send'] = 'Send';
$string['viewstats'] = 'View stats';
$string['confirmdeletecampaign'] = 'Are you sure you want to delete this campaign?';
$string['nocampaigns'] = 'No campaigns created';
$string['backtocampaigns'] = 'Back to campaigns';

// Campaign statistics (Phase 8).
$string['campaignstatistics'] = 'Campaign statistics';
$string['totalsent'] = 'Total sent';
$string['openrate'] = 'Open rate';
$string['clickrate'] = 'Click rate';
$string['conversionrate'] = 'Conversion rate';
$string['opens'] = 'opens';
$string['clicks'] = 'clicks';
$string['conversions'] = 'conversions';
$string['senttime'] = 'Sent time';
$string['abtestingresults'] = 'A/B testing results';
$string['variant'] = 'Variant';
$string['sent'] = 'Sent';
$string['opened'] = 'Opened';
$string['clicked'] = 'Clicked';
$string['converted'] = 'Converted';
$string['winner'] = 'Winner';
$string['tie'] = 'Tie';
$string['performancedifference'] = 'Performance difference: {$a->difference}%';
$string['performancecharts'] = 'Performance charts';
$string['conversionfunnel'] = 'Conversion funnel';
$string['abcomparison'] = 'A/B comparison';
$string['recipientbreakdown'] = 'Recipient breakdown';
$string['recipient'] = 'Recipient';
$string['exportoptions'] = 'Export options';
$string['exporttocsv'] = 'Export to CSV';
$string['norecipients'] = 'No recipients';

// Gamification (Phase 8).
$string['gamification'] = 'Gamification';
$string['leaderboard'] = 'Leaderboard';
$string['points'] = 'Points';
$string['level'] = 'Level';
$string['achievements'] = 'Achievements';
$string['currentstreak'] = 'Current streak';
$string['longeststreak'] = 'Longest streak';
$string['yourstats'] = 'Your statistics';
$string['progresstonextlevel'] = 'Progress to next level';
$string['pointstonextlevel'] = '{$a->current} / {$a->needed} points';
$string['topleaders'] = 'Top leaders';
$string['rank'] = 'Rank';
$string['student'] = 'Student';
$string['streak'] = 'Streak';
$string['you'] = 'You';
$string['leveln'] = 'Level {$a->level}';
$string['recentachievements'] = 'Recent achievements';
$string['noachievements'] = 'No recent achievements';
$string['noleaderboarddata'] = 'No leaderboard data';
$string['earned'] = 'earned';
$string['period_all'] = 'All';
$string['period_month'] = 'This month';
$string['period_week'] = 'This week';

// Achievement names (Phase 8).
$string['achievement_first_login'] = 'First steps';
$string['achievement_week_streak'] = 'Week streak';
$string['achievement_month_streak'] = 'Full month';
$string['achievement_all_assignments'] = 'All assignments';
$string['achievement_early_submitter'] = 'Early submitter';
$string['achievement_helper'] = 'Good classmate';
$string['achievement_improvement'] = 'Notable improvement';
$string['achievement_risk_recovery'] = 'Spectacular recovery';

// Mobile API (Phase 8).
$string['mobileapi'] = 'Mobile API';
$string['apienabled'] = 'API enabled';
$string['apikey'] = 'API key';
$string['apidocumentation'] = 'API documentation';
$string['endpoints'] = 'Endpoints';
$string['endpoint_getstats'] = 'Get student statistics';
$string['endpoint_getgamification'] = 'Get gamification data';
$string['endpoint_getleaderboard'] = 'Get leaderboard';
$string['endpoint_getcampaignstats'] = 'Get campaign statistics';

// Additional strings (Phase 8).
$string['backtodashboard'] = 'Back to dashboard';
$string['filter_risklevel'] = 'Risk level';
$string['filter_inactivitydays'] = 'Inactivity days';
$string['filter_missingassignments'] = 'Missing assignments';
$string['filter_lastlogin'] = 'Last login';
$string['filter_supervisor'] = 'Supervisor';
$string['filter_course'] = 'Course';
$string['campaign_create'] = 'Create campaign';
$string['campaign_edit'] = 'Edit campaign';
$string['campaign_delete'] = 'Delete campaign';
$string['variant_a'] = 'Variant A';
$string['variant_b'] = 'Variant B';
$string['enable_abtesting'] = 'Enable A/B testing';
$string['abtest_splitratio'] = 'Split ratio';
$string['target_all'] = 'All students';
$string['target_atrisk'] = 'At-risk students';
$string['target_critical'] = 'Critical risk';
$string['target_high'] = 'High risk';
$string['target_medium'] = 'Medium risk';
$string['target_low'] = 'Low risk';
$string['send_immediately'] = 'Send immediately';
$string['schedule_later'] = 'Schedule for later';
$string['campaign_scheduled'] = 'Campaign scheduled successfully';
$string['campaign_created'] = 'Campaign created successfully';
$string['pointsawarded'] = '{$a} points awarded';
$string['levelup'] = 'Level up! New level: {$a}';
$string['streakbonus'] = 'Streak bonus: +{$a} points';
$string['achievementunlocked'] = 'Achievement unlocked: {$a}';

// Student Self-Service Portal & AI Recommendations (Phase 9).
$string['studentdashboard'] = 'Student Dashboard';
$string['welcomeback'] = 'Welcome back {$a}!';
$string['yourrisk'] = 'Your risk level';
$string['yourpoints'] = 'Your points';
$string['yourstreak'] = 'Your streak';
$string['noriskdata'] = 'No risk data available';
$string['personalizedrecommendations'] = 'Personalized Recommendations';
$string['norecommendations'] = 'No recommendations at this time';
$string['keepupgoodwork'] = 'Keep up the good work!';
$string['impact'] = 'Impact';
$string['takeaction'] = 'Take action';
$string['yourprogress'] = 'Your Progress';
$string['activitythisweek'] = 'Activity this week';
$string['performancetrend'] = 'Performance trend';
$string['quickactions'] = 'Quick Actions';
$string['viewleaderboard'] = 'View Leaderboard';
$string['viewcalendar'] = 'View Calendar';
$string['viewcourses'] = 'View My Courses';
$string['goto'] = 'Go to';
$string['noachievementsyet'] = 'No achievements yet. Start learning!';
$string['tipsandmotivation'] = 'Tips & Motivation';
$string['missing'] = 'missing';

// AI Recommendations.
$string['rec_increase_login'] = 'Increase your login frequency';
$string['rec_increase_login_desc'] = 'You logged in {$a->current} times this month. Try to reach {$a->target} logins to stay engaged.';
$string['rec_study_consistency'] = 'Improve study consistency';
$string['rec_study_consistency_desc'] = 'Try to log in more regularly (at least every 2 days) to maintain a consistent learning pace.';
$string['rec_optimal_study_time'] = 'Optimize your study schedule';
$string['rec_optimal_study_time_desc'] = 'Consider studying during daytime hours (8am-10pm) for better concentration.';
$string['rec_urgent_assignment'] = 'Urgent assignment due';
$string['rec_urgent_assignment_desc'] = '{$a->name} in {$a->course} is due on {$a->duedate}. Don\'t miss this deadline!';
$string['rec_explore_resources'] = 'Explore unviewed resources';
$string['rec_explore_resources_desc'] = 'You have {$a->count} unviewed resources. Exploring these could improve your understanding.';
$string['rec_forum_participation'] = 'Participate in forum discussions';
$string['rec_forum_participation_desc'] = 'Join discussions to learn from peers and share your knowledge.';
$string['rec_help_peers'] = 'Help your peers';
$string['rec_help_peers_desc'] = 'With your strong performance, you could help other students in forums. It\'s great for learning!';
$string['rec_catch_up_plan'] = 'Catch-up plan needed';
$string['rec_catch_up_plan_desc'] = 'You have {$a->count} overdue assignments. Create a plan to catch up gradually.';
$string['rec_use_calendar'] = 'Use the Moodle calendar';
$string['rec_use_calendar_desc'] = 'The calendar helps you stay organized and never miss a deadline.';
$string['rec_increase_engagement'] = 'Increase your engagement';
$string['rec_increase_engagement_desc'] = 'Your weekly activity is {$a->current}. Try to reach {$a->target} activities per week.';
$string['rec_check_leaderboard'] = 'Check the leaderboard';
$string['rec_check_leaderboard_desc'] = 'You\'re active! Check your position on the leaderboard and earn more points.';

// Peer comparison.
$string['peercomparison'] = 'Peer Comparison';
$string['peercomparison_desc'] = 'Anonymously compare your performance with other students in your courses.';
$string['yourperformance'] = 'Your Performance';
$string['percentile'] = 'th percentile';
$string['comparedto'] = 'Compared to {$a} other students in your courses';
$string['performanceradar'] = 'Performance Radar';
$string['detailedmetrics'] = 'Detailed Metrics';
$string['loginfrequency'] = 'Login Frequency';
$string['assignmentcompletion'] = 'Assignment Completion';
$string['engagement'] = 'Engagement';
$string['gradeperformance'] = 'Grade Performance';
$string['yourvalue'] = 'Your value';
$string['peeraverage'] = 'Peer average';
$string['percentileposition'] = 'Percentile position';
$string['logins'] = 'logins';
$string['activities'] = 'activities';
$string['insights'] = 'Insights';
$string['category_top'] = 'Exceptional Performance';
$string['category_above_average'] = 'Above Average';
$string['category_average'] = 'Average Performance';
$string['category_below_average'] = 'Below Average';
$string['category_needs_improvement'] = 'Needs Improvement';
$string['insight_top_performer'] = 'Congratulations! You\'re in the top 25% of your peers. Excellent work!';
$string['insight_above_average'] = 'You\'re above average. Keep it up!';
$string['insight_room_for_improvement'] = 'You have room for improvement. Check out the personalized recommendations.';
$string['insight_needs_boost'] = 'Time to boost your studies! Start with the recommendations above.';
$string['improvement_suggestion_login'] = 'Tip: Log in more regularly to stay up-to-date with your courses.';
$string['improvement_suggestion_assignment'] = 'Tip: Focus on completing assignments on time.';
$string['improvement_suggestion_engagement'] = 'Tip: Participate more actively in course activities.';
$string['improvement_suggestion_grade'] = 'Tip: Ask teachers or peers for help to improve your grades.';
$string['privacy_note'] = 'All comparisons are anonymous. Your peers cannot see your individual data.';

// Goals and progress tracking.
$string['mygoals'] = 'My Goals';
$string['totalgoals'] = 'Total Goals';
$string['activegoals'] = 'Active Goals';
$string['completedgoals'] = 'Completed Goals';
$string['completionrate'] = 'Completion Rate';
$string['suggestedgoals'] = 'Suggested Goals';
$string['createthisgoal'] = 'Create this goal';
$string['noactivegoals'] = 'No active goals. Create one to track your progress!';
$string['daysremaining'] = 'days remaining';
$string['progress'] = 'Progress';
$string['completedon'] = 'Completed on {$a}';
$string['createcustomgoal'] = 'Create Custom Goal';
$string['customgoal_desc'] = 'Define your own goals to stay motivated and track your progress.';
$string['goaltitle'] = 'Goal Title';
$string['goaldescription'] = 'Description';
$string['targetvalue'] = 'Target Value';
$string['deadline'] = 'Deadline';
$string['creategoal'] = 'Create Goal';
$string['goalcreated'] = 'Goal created successfully!';
$string['goal_complete_assignments'] = 'Catch up on overdue assignments';
$string['goal_complete_assignments_desc'] = 'Complete all your missing assignments to improve your performance.';
$string['goal_increase_logins'] = 'Increase login frequency';
$string['goal_increase_logins_desc'] = 'Log in more regularly to stay engaged with your courses.';
$string['goal_completed'] = 'Goal completed: {$a}';

// Risk explanations.
$string['riskexplanation_faible'] = 'You\'re on track! Keep up the good work.';
$string['riskexplanation_moyen'] = 'Be careful to maintain your engagement.';
$string['riskexplanation_élevé'] = 'Need improvement. Check recommendations.';
$string['riskexplanation_critique'] = 'Immediate action required. Contact your supervisor.';

// Tips for students.
$string['tip1'] = '💡 Tip: Log in daily for 15 minutes to stay up-to-date.';
$string['tip2'] = '📚 Tip: Create a weekly study schedule and stick to it.';
$string['tip3'] = '🎯 Motivation: Every bit of progress counts. Celebrate your wins!';
$string['tip4'] = '🤝 Tip: Don\'t hesitate to ask classmates or teachers for help.';
$string['tip5'] = '⏰ Reminder: Manage your time effectively by prioritizing important tasks.';
