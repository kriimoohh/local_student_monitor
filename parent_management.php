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
 * Parent/Guardian management page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managesettings', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$studentid = optional_param('studentid', 0, PARAM_INT);
$parentid = optional_param('parentid', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/parent_management.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('parentmanagement', 'local_student_monitor'));
$PAGE->set_heading(get_string('parentmanagement', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$parentmanager = new \local_student_monitor\manager\parent_guardian_manager();

// Handle actions.
if ($action && confirm_sesskey()) {
    switch ($action) {
        case 'add':
            $parentname = required_param('parentname', PARAM_TEXT);
            $parentemail = required_param('parentemail', PARAM_EMAIL);
            $parentphone = optional_param('parentphone', '', PARAM_TEXT);
            $relationship = optional_param('relationship', 'parent', PARAM_TEXT);

            $parentmanager->register_parent($studentid, $parentname, $parentemail, $parentphone, $relationship);
            redirect($PAGE->url, get_string('parentadded', 'local_student_monitor'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        case 'delete':
            if ($parentid) {
                $DB->delete_records('local_sm_parents', ['id' => $parentid]);
                redirect($PAGE->url, get_string('parentdeleted', 'local_student_monitor'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;

        case 'notify':
            if ($studentid) {
                $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $studentid]);
                if ($tracking) {
                    $results = $parentmanager->notify_parents_critical($studentid, $tracking->risk_level, $tracking);
                    $message = count($results) . ' ' . get_string('parentsnotified', 'local_student_monitor');
                    redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
                }
            }
            break;
    }
}

// Get statistics.
$stats = $parentmanager->get_parent_notification_stats();

// Get all students with registered parents.
$studentswitparents = $DB->get_records_sql("
    SELECT DISTINCT st.userid,
           u.firstname,
           u.lastname,
           u.email,
           st.risk_level
    FROM {local_sm_student_tracking} st
    JOIN {user} u ON u.id = st.userid
    WHERE st.userid IN (
        SELECT DISTINCT student_id
        FROM {local_sm_parents}
    )
    ORDER BY u.lastname, u.firstname
");

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('parentmanagement', 'local_student_monitor'));

// Statistics KPIs.
echo html_writer::start_div('row mb-4');

// Total registered.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('registeredparents', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_registered, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Notifications sent this month.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('notificationsthismonth', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_notifications, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Unique parents notified.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('uniqueparentsnotified', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->unique_parents, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Add parent form.
echo html_writer::tag('h3', get_string('addparent', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url->out(false),
    'class' => 'mform'
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey()
]);

echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'action',
    'value' => 'add'
]);

echo html_writer::start_div('row');

// Student selector.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', get_string('student', 'local_student_monitor'));
echo html_writer::start_tag('select', [
    'name' => 'studentid',
    'class' => 'form-control',
    'required' => 'required'
]);
echo html_writer::tag('option', get_string('selectstudent', 'local_student_monitor'), ['value' => '']);

$students = $DB->get_records('local_sm_student_tracking', null, 'userid', 'userid');
foreach ($students as $student) {
    $user = $DB->get_record('user', ['id' => $student->userid]);
    if ($user) {
        echo html_writer::tag('option', fullname($user), ['value' => $user->id]);
    }
}

echo html_writer::end_tag('select');
echo html_writer::end_div();

// Parent name.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', get_string('parentname', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'parentname',
    'class' => 'form-control',
    'required' => 'required'
]);
echo html_writer::end_div();

// Parent email.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', get_string('parentemail', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'email',
    'name' => 'parentemail',
    'class' => 'form-control',
    'required' => 'required'
]);
echo html_writer::end_div();

// Relationship.
echo html_writer::start_div('col-md-2');
echo html_writer::tag('label', get_string('relationship', 'local_student_monitor'));
echo html_writer::start_tag('select', [
    'name' => 'relationship',
    'class' => 'form-control'
]);
echo html_writer::tag('option', get_string('parent', 'local_student_monitor'), ['value' => 'parent']);
echo html_writer::tag('option', get_string('guardian', 'local_student_monitor'), ['value' => 'guardian']);
echo html_writer::tag('option', get_string('tutor', 'local_student_monitor'), ['value' => 'tutor']);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Submit button.
echo html_writer::start_div('col-md-1');
echo html_writer::tag('label', '&nbsp;');
echo html_writer::tag('button', get_string('add', 'local_student_monitor'), [
    'type' => 'submit',
    'class' => 'btn btn-primary btn-block'
]);
echo html_writer::end_div();

echo html_writer::end_div(); // Row.
echo html_writer::end_tag('form');

// Students with registered parents.
echo html_writer::tag('h3', get_string('studentswitparents', 'local_student_monitor'), ['class' => 'mt-4']);

if (empty($studentswitparents)) {
    echo html_writer::div(
        get_string('noparentsregistered', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('student', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('risklevel', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('registeredparents', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($studentswitparents as $student) {
        echo html_writer::start_tag('tr');

        // Student name.
        echo html_writer::start_tag('td');
        $studenturl = new moodle_url('/user/profile.php', ['id' => $student->userid]);
        echo html_writer::link($studenturl, fullname($student));
        echo html_writer::tag('br');
        echo html_writer::tag('small', $student->email, ['class' => 'text-muted']);
        echo html_writer::end_tag('td');

        // Risk level.
        $riskclass = [
            'CRITICAL' => 'badge-danger',
            'HIGH' => 'badge-warning',
            'MEDIUM' => 'badge-info',
            'LOW' => 'badge-success'
        ][$student->risk_level] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', $student->risk_level, ['class' => 'badge ' . $riskclass]);
        echo html_writer::end_tag('td');

        // Registered parents.
        $parents = $parentmanager->get_student_parents($student->userid);
        echo html_writer::start_tag('td');
        foreach ($parents as $parent) {
            echo html_writer::tag('div', $parent->parent_name . ' (' . $parent->parent_email . ')');
        }
        echo html_writer::end_tag('td');

        // Actions.
        echo html_writer::start_tag('td');
        $notifyurl = new moodle_url($PAGE->url, [
            'action' => 'notify',
            'studentid' => $student->userid,
            'sesskey' => sesskey()
        ]);
        echo html_writer::link($notifyurl, get_string('notifyparents', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-primary']);
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
