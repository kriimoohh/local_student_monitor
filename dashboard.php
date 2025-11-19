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
 * Student Monitor Dashboard.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:viewdashboard', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/dashboard.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('studentmonitordashboard', 'local_student_monitor'));
$PAGE->set_heading(get_string('studentmonitordashboard', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Add CSS.
$PAGE->requires->css('/local/student_monitor/styles/styles.css');

// Add JavaScript.
$PAGE->requires->js_call_amd('local_student_monitor/dashboard', 'init');

// Get filter parameters.
$risklevel = optional_param('risk', '', PARAM_ALPHA);
$courseid = optional_param('course', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);

// Initialize managers.
$tracker = new \local_student_monitor\manager\student_tracker();
$notificationmanager = new \local_student_monitor\manager\notification_manager();

// Get statistics.
$stats = $tracker->get_statistics();

// Get notifications stats.
$weekago = time() - (7 * 86400);
$notificationsstats = $DB->get_record_sql(
    "SELECT COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
       FROM {local_sm_notifications}
      WHERE timecreated >= :weekago",
    ['weekago' => $weekago]
);

// Calculate read rate.
$readrate = 0;
if ($notificationsstats->sent > 0) {
    $readrate = round(($notificationsstats->readcount / $notificationsstats->sent) * 100, 1);
}

// Get students at risk.
$studentsatrisk = $tracker->get_students_at_risk($risklevel, 50);

// Get critical alerts.
$criticalalerts = $DB->get_records_sql(
    "SELECT st.*, u.firstname, u.lastname, u.email
       FROM {local_sm_student_tracking} st
       JOIN {user} u ON u.id = st.userid
      WHERE st.intervention_needed = 1
        AND st.risk_level IN ('CRITIQUE', 'ÉLEVÉ')
   ORDER BY CASE st.risk_level
                WHEN 'CRITIQUE' THEN 1
                WHEN 'ÉLEVÉ' THEN 2
                ELSE 3
            END,
            st.inactivity_days DESC
      LIMIT 5"
);

// Output starts here.
echo $OUTPUT->header();

// Display success messages.
if ($message = optional_param('message', '', PARAM_TEXT)) {
    echo $OUTPUT->notification(get_string($message, 'local_student_monitor'), 'success');
}

// KPI Cards.
echo html_writer::start_div('student-monitor-dashboard');

// Top KPI row.
echo html_writer::start_div('row mb-3');

// Card 1: Students at risk.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-danger text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('studentsatrisk', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->critique + $stats->eleve, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('risk_critique', 'local_student_monitor') . ': ' . $stats->critique . ' | ' .
    get_string('risk_eleve', 'local_student_monitor') . ': ' . $stats->eleve);
echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.
echo html_writer::end_div(); // col.

// Card 2: Notifications sent (this week).
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('notificationssent', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $notificationsstats->sent, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('weeklyreport', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Card 3: Interventions needed.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-dark');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('interventionsneeded', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->intervention_needed, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('interventionneeded', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Card 4: Read rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('readrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $readrate . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', $notificationsstats->readcount . ' / ' . $notificationsstats->sent);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // row.

// Critical Alerts Section.
if (!empty($criticalalerts)) {
    echo html_writer::start_div('alert alert-danger mt-3');
    echo html_writer::tag('h4', '🚨 ' . get_string('criticalalerts', 'local_student_monitor'));
    echo html_writer::start_tag('ul');
    foreach ($criticalalerts as $alert) {
        $msg = fullname($alert) . ' (' . $alert->email . ') - ' .
               get_string('risklevel', 'local_student_monitor') . ': ' . $alert->risk_level . ' - ' .
               $alert->inactivity_days . ' ' . get_string('inactivitydays', 'local_student_monitor');
        echo html_writer::tag('li', $msg);
    }
    echo html_writer::end_tag('ul');
    echo html_writer::end_div();
}

// Automatic Alerts Configuration Card.
$automaticalertsenabled = get_config('local_student_monitor', 'automatic_alerts_enabled');
if (has_capability('local/student_monitor:managesettings', $context)) {
    echo html_writer::start_div('alert ' . ($automaticalertsenabled ? 'alert-success' : 'alert-warning') . ' mb-3');
    echo html_writer::start_div('d-flex justify-content-between align-items-center');
    echo html_writer::start_div();
    echo html_writer::tag('strong', $automaticalertsenabled ?
        '✅ ' . get_string('automaticalertsenabled', 'local_student_monitor') :
        '⚠️ ' . get_string('automaticalertsdisabled', 'local_student_monitor'));
    echo html_writer::tag('p', get_string('automaticalertsinfo', 'local_student_monitor'),
        ['class' => 'mb-0 mt-1']);
    echo html_writer::end_div();
    $configureurl = new moodle_url('/local/student_monitor/configure_automatic_alerts.php');
    echo html_writer::link($configureurl, '⚙️ ' . get_string('configurealerts', 'local_student_monitor'),
        ['class' => 'btn btn-sm ' . ($automaticalertsenabled ? 'btn-outline-success' : 'btn-outline-warning')]);
    echo html_writer::end_div();
    echo html_writer::end_div();
}

// Quick Actions.
echo html_writer::start_div('mt-3 mb-3');
$createalerturl = new moodle_url('/local/student_monitor/create_alert.php');
$viewalertsurl = new moodle_url('/local/student_monitor/view_alerts.php');
$studentsatriskurl = new moodle_url('/local/student_monitor/students_at_risk.php');

echo html_writer::link($studentsatriskurl, '⚠️ ' . get_string('studentsatrisk', 'local_student_monitor'),
    ['class' => 'btn btn-danger mr-2']);
echo html_writer::link($createalerturl, get_string('createalert', 'local_student_monitor'),
    ['class' => 'btn btn-primary mr-2']);
echo html_writer::link($viewalertsurl, get_string('weeklyreport', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mr-2']);

// Export button.
$exporturl = new moodle_url('/local/student_monitor/export.php', ['format' => 'csv']);
echo html_writer::link($exporturl, get_string('exportcsv', 'local_student_monitor'),
    ['class' => 'btn btn-success']);

echo html_writer::end_div();

// Filters.
echo html_writer::start_div('card mt-3 mb-3');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('studentlist', 'local_student_monitor'), ['class' => 'card-title']);

// Filter form.
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $PAGE->url, 'class' => 'form-inline']);

echo html_writer::tag('label', get_string('risklevel', 'local_student_monitor') . ':', ['class' => 'mr-2']);
echo html_writer::select(
    [
        '' => get_string('all'),
        'CRITIQUE' => get_string('risk_critique', 'local_student_monitor'),
        'ÉLEVÉ' => get_string('risk_eleve', 'local_student_monitor'),
        'MOYEN' => get_string('risk_moyen', 'local_student_monitor'),
        'FAIBLE' => get_string('risk_faible', 'local_student_monitor'),
    ],
    'risk',
    $risklevel,
    [],
    ['class' => 'custom-select mr-2']
);

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('filter'),
    'class' => 'btn btn-primary',
]);

echo html_writer::end_tag('form');
echo html_writer::end_div();
echo html_writer::end_div();

// Students table.
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');

if (!empty($studentsatrisk)) {
    $table = new html_table();
    $table->head = [
        get_string('risklevel', 'local_student_monitor'),
        get_string('studentname', 'local_student_monitor'),
        get_string('email'),
        get_string('lastactivity', 'local_student_monitor'),
        get_string('inactivitydays', 'local_student_monitor'),
        get_string('missingassignments', 'local_student_monitor'),
        get_string('notificationcount', 'local_student_monitor'),
        get_string('actions', 'local_student_monitor'),
    ];

    $table->attributes['class'] = 'table table-striped';

    foreach ($studentsatrisk as $student) {
        // Risk badge.
        $riskclass = 'badge ';
        switch ($student->risk_level) {
            case 'CRITIQUE':
                $riskclass .= 'badge-danger';
                break;
            case 'ÉLEVÉ':
                $riskclass .= 'badge-warning';
                break;
            case 'MOYEN':
                $riskclass .= 'badge-info';
                break;
            default:
                $riskclass .= 'badge-success';
        }
        $riskbadge = html_writer::tag('span', $student->risk_level, ['class' => $riskclass]);

        // Last activity.
        $lastactivity = $student->last_activity ? userdate($student->last_activity, get_string('strftimedatetime')) : '-';

        // Actions.
        $actions = '';
        $viewurl = new moodle_url('/user/profile.php', ['id' => $student->userid]);
        $actions .= html_writer::link($viewurl, get_string('view'), ['class' => 'btn btn-sm btn-primary mr-1']);

        $row = [
            $riskbadge,
            fullname($student),
            $student->email,
            $lastactivity,
            $student->inactivity_days,
            $student->missing_assignments,
            $student->notification_count,
            $actions,
        ];

        $table->data[] = $row;
    }

    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('nostudents', 'local_student_monitor'), ['class' => 'alert alert-info']);
}

echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // dashboard.

echo $OUTPUT->footer();
