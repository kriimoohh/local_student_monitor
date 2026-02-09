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
 * Bulk actions page for managing multiple students.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:intervene', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$userids = optional_param_array('userids', [], PARAM_INT);
$supervisorid = optional_param('supervisorid', 0, PARAM_INT);
$note = optional_param('note', '', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/bulk_actions.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('bulkactions', 'local_student_monitor'));
$PAGE->set_heading(get_string('bulkactions', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$tracker = new \local_student_monitor\manager\student_tracker();

// Process bulk action.
if ($action && !empty($userids) && confirm_sesskey()) {

    if (!$confirm) {
        // Show confirmation page.
        echo $OUTPUT->header();

        $usercount = count($userids);
        $actionstring = get_string('bulkaction_' . $action, 'local_student_monitor');

        echo html_writer::tag('h2', get_string('confirmaction', 'local_student_monitor'));
        echo html_writer::tag('p', get_string('confirmactionmsg', 'local_student_monitor',
            ['action' => $actionstring, 'count' => $usercount]), ['class' => 'alert alert-warning']);

        // Build confirm URL.
        $confirmurl = new moodle_url('/local/student_monitor/bulk_actions.php', [
            'action' => $action,
            'confirm' => 1,
            'sesskey' => sesskey()
        ]);

        foreach ($userids as $userid) {
            $confirmurl->param('userids[]', $userid);
        }

        if ($supervisorid) {
            $confirmurl->param('supervisorid', $supervisorid);
        }

        if ($note) {
            $confirmurl->param('note', $note);
        }

        echo html_writer::start_div('mt-3');
        echo html_writer::link($confirmurl, get_string('confirm'), ['class' => 'btn btn-danger mr-2']);

        $cancelurl = new moodle_url('/local/student_monitor/dashboard.php');
        echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary']);
        echo html_writer::end_div();

        echo $OUTPUT->footer();
        exit;
    }

    // Execute bulk action.
    $success = 0;
    $failed = 0;

    switch ($action) {
        case 'assign':
            if ($supervisorid) {
                foreach ($userids as $userid) {
                    try {
                        $tracker->assign_to_supervisor($userid, $supervisorid);
                        $success++;
                    } catch (Exception $e) {
                        $failed++;
                    }
                }
            }
            break;

        case 'addnote':
            if ($note) {
                foreach ($userids as $userid) {
                    try {
                        $tracker->add_notes($userid, $note);
                        $success++;
                    } catch (Exception $e) {
                        $failed++;
                    }
                }
            }
            break;

        case 'unassign':
            foreach ($userids as $userid) {
                try {
                    $tracker->assign_to_supervisor($userid, null);
                    $success++;
                } catch (Exception $e) {
                    $failed++;
                }
            }
            break;

        case 'notify':
            $notificationmanager = new \local_student_monitor\manager\notification_manager();
            foreach ($userids as $userid) {
                try {
                    $user = $DB->get_record('user', ['id' => $userid]);
                    $subject = get_string('bulknotificationsubject', 'local_student_monitor');
                    $message = $note ? $note : get_string('bulknotificationmessage', 'local_student_monitor');

                    $notificationmanager->create_notification(
                        $userid,
                        'manualalert',
                        $subject,
                        $message,
                        0,
                        ['email', 'moodle']
                    );
                    $success++;
                } catch (Exception $e) {
                    $failed++;
                }
            }
            break;
    }

    // Redirect with message.
    $message = get_string('bulkactionsuccess', 'local_student_monitor', ['success' => $success, 'failed' => $failed]);
    redirect(
        new moodle_url('/local/student_monitor/dashboard.php'),
        $message,
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Display bulk actions form.
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('bulkactions', 'local_student_monitor'));
echo html_writer::tag('p', get_string('bulkactionsdesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

// Get all supervisors.
$supervisors = get_users_by_capability(
    context_system::instance(),
    'local/student_monitor:intervene',
    'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename'
);

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => new moodle_url('/local/student_monitor/bulk_actions.php'),
    'class' => 'mt-4'
]);

echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

// Action selection.
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('selectaction', 'local_student_monitor'), ['for' => 'action']);
echo html_writer::start_tag('select', [
    'name' => 'action',
    'id' => 'action',
    'class' => 'form-control',
    'required' => 'required'
]);
echo html_writer::tag('option', get_string('choosedots', 'local_student_monitor'), ['value' => '']);
echo html_writer::tag('option', get_string('bulkaction_assign', 'local_student_monitor'), ['value' => 'assign']);
echo html_writer::tag('option', get_string('bulkaction_unassign', 'local_student_monitor'), ['value' => 'unassign']);
echo html_writer::tag('option', get_string('bulkaction_addnote', 'local_student_monitor'), ['value' => 'addnote']);
echo html_writer::tag('option', get_string('bulkaction_notify', 'local_student_monitor'), ['value' => 'notify']);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Supervisor selection (for assign action).
echo html_writer::start_div('form-group supervisor-field', ['style' => 'display: none;']);
echo html_writer::tag('label', get_string('selectsupervisor', 'local_student_monitor'), ['for' => 'supervisorid']);
echo html_writer::start_tag('select', [
    'name' => 'supervisorid',
    'id' => 'supervisorid',
    'class' => 'form-control'
]);
echo html_writer::tag('option', get_string('choosedots', 'local_student_monitor'), ['value' => '0']);
foreach ($supervisors as $supervisor) {
    echo html_writer::tag('option', fullname($supervisor), ['value' => $supervisor->id]);
}
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Note/message field (for addnote and notify actions).
echo html_writer::start_div('form-group note-field', ['style' => 'display: none;']);
echo html_writer::tag('label', get_string('noteormessage', 'local_student_monitor'), ['for' => 'note']);
echo html_writer::tag('textarea', '', [
    'name' => 'note',
    'id' => 'note',
    'class' => 'form-control',
    'rows' => '4'
]);
echo html_writer::end_div();

// Student selection.
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('selectstudents', 'local_student_monitor'));

// Get all at-risk students.
$students = $tracker->get_students_at_risk(null, 0);

if (empty($students)) {
    echo html_writer::tag('p', get_string('nostudents', 'local_student_monitor'), ['class' => 'text-muted']);
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', html_writer::tag('input', '', [
        'type' => 'checkbox',
        'id' => 'selectall'
    ]));
    echo html_writer::tag('th', get_string('studentname', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('risklevel', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('inactivitydays', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($students as $student) {
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td');
        echo html_writer::tag('input', '', [
            'type' => 'checkbox',
            'name' => 'userids[]',
            'value' => $student->userid,
            'class' => 'student-checkbox'
        ]);
        echo html_writer::end_tag('td');
        echo html_writer::tag('td', $student->fullname);
        $riskclass = local_student_monitor_get_risk_class($student->risk_level);
        echo html_writer::tag('td', html_writer::tag('span', $student->risk_level, ['class' => 'badge ' . $riskclass]));
        echo html_writer::tag('td', $student->inactivity_days);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}
echo html_writer::end_div();

// Submit buttons.
echo html_writer::start_div('form-group');
echo html_writer::tag('button', get_string('executeaction', 'local_student_monitor'), [
    'type' => 'submit',
    'class' => 'btn btn-primary'
]);
$cancelurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary ml-2']);
echo html_writer::end_div();

echo html_writer::end_tag('form');

// JavaScript for dynamic form fields.
$PAGE->requires->js_amd_inline("
require(['jquery'], function($) {
    $('#action').change(function() {
        var action = $(this).val();
        $('.supervisor-field').hide();
        $('.note-field').hide();

        if (action === 'assign') {
            $('.supervisor-field').show();
            $('#supervisorid').prop('required', true);
        } else {
            $('#supervisorid').prop('required', false);
        }

        if (action === 'addnote' || action === 'notify') {
            $('.note-field').show();
            $('#note').prop('required', true);
        } else {
            $('#note').prop('required', false);
        }
    });

    $('#selectall').change(function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
    });
});
");

echo $OUTPUT->footer();
