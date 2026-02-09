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
 * Task management page for supervisors.
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
$taskid = optional_param('taskid', 0, PARAM_INT);
$status = optional_param('status', 'all', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/student_monitor/tasks.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('taskmanagement', 'local_student_monitor'));
$PAGE->set_heading(get_string('taskmanagement', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Handle task actions.
if ($action && $taskid && confirm_sesskey()) {
    $interventiontracker = new \local_student_monitor\manager\intervention_tracker();

    switch ($action) {
        case 'complete':
            $interventiontracker->complete_task($taskid, $USER->id);
            redirect($PAGE->url, get_string('taskcompleted', 'local_student_monitor'), null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        case 'defer':
            $newduedate = required_param('duedate', PARAM_INT);
            $interventiontracker->defer_task($taskid, $newduedate);
            redirect($PAGE->url, get_string('taskdeferred', 'local_student_monitor'), null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        case 'reassign':
            $newsupervisor = required_param('supervisor', PARAM_INT);
            $interventiontracker->reassign_task($taskid, $newsupervisor);
            redirect($PAGE->url, get_string('taskreassigned', 'local_student_monitor'), null, \core\output\notification::NOTIFY_SUCCESS);
            break;
    }
}

// Get tasks for current supervisor.
$tasks = $DB->get_records_sql("
    SELECT t.*,
           u.firstname,
           u.lastname,
           u.email,
           u.firstnamephonetic,
           u.lastnamephonetic,
           u.middlename,
           u.alternatename,
           st.risk_level,
           st.inactivity_days,
           st.missing_activities
    FROM {local_sm_tasks} t
    JOIN {user} u ON u.id = t.student_id
    LEFT JOIN {local_sm_student_tracking} st ON st.userid = u.id
    WHERE t.supervisor_id = :supervisorid
    " . ($status != 'all' ? " AND t.status = :status" : "") . "
    ORDER BY
        CASE t.priority
            WHEN 'urgent' THEN 1
            WHEN 'high' THEN 2
            WHEN 'normal' THEN 3
            WHEN 'low' THEN 4
        END,
        t.due_date ASC
", array_merge(
    ['supervisorid' => $USER->id],
    $status != 'all' ? ['status' => $status] : []
));

// Calculate task statistics.
$stats = new stdClass();
$stats->total = 0;
$stats->pending = 0;
$stats->in_progress = 0;
$stats->completed = 0;
$stats->overdue = 0;

foreach ($tasks as $task) {
    $stats->total++;

    if ($task->status == 'pending') {
        $stats->pending++;
    } else if ($task->status == 'in_progress') {
        $stats->in_progress++;
    } else if ($task->status == 'completed') {
        $stats->completed++;
    }

    if ($task->status != 'completed' && $task->due_date < time()) {
        $stats->overdue++;
    }
}

// Initialize JavaScript module.
$PAGE->requires->js_call_amd('local_student_monitor/task_manager', 'init');

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('taskmanagement', 'local_student_monitor'));

// Status filter.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('filterbystatus', 'local_student_monitor') . ': ', ['for' => 'status-filter']);
echo html_writer::start_tag('select', [
    'id' => 'status-filter',
    'class' => 'custom-select',
    'onchange' => 'window.location.href="?status=" + this.value'
]);
echo html_writer::tag('option', get_string('all', 'local_student_monitor'),
    ['value' => 'all', 'selected' => ($status === 'all')]);
echo html_writer::tag('option', get_string('pending', 'local_student_monitor'),
    ['value' => 'pending', 'selected' => ($status === 'pending')]);
echo html_writer::tag('option', get_string('inprogress', 'local_student_monitor'),
    ['value' => 'in_progress', 'selected' => ($status === 'in_progress')]);
echo html_writer::tag('option', get_string('completed', 'local_student_monitor'),
    ['value' => 'completed', 'selected' => ($status === 'completed')]);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Task statistics KPIs.
echo html_writer::start_div('row mb-4');

// Total tasks.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totaltasks', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Pending tasks.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('pendingtasks', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->pending, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// In progress.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('inprogresstasks', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->in_progress, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Overdue tasks.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-danger text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('overduetasks', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->overdue, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Tasks table.
if (empty($tasks)) {
    echo html_writer::div(
        get_string('notasksfound', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped table-hover']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('student', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('tasktype', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('priority', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('risklevel', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('duedate', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('status', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($tasks as $task) {
        $studentname = fullname($task);
        $isoverdue = ($task->status != 'completed' && $task->due_date < time());

        echo html_writer::start_tag('tr', ['class' => $isoverdue ? 'table-danger' : '']);

        // Student name with link.
        $studenturl = new moodle_url('/user/profile.php', ['id' => $task->student_id]);
        echo html_writer::start_tag('td');
        echo html_writer::link($studenturl, $studentname);
        echo html_writer::tag('br');
        echo html_writer::tag('small', $task->email, ['class' => 'text-muted']);
        echo html_writer::end_tag('td');

        // Task type.
        echo html_writer::tag('td', get_string('tasktype_' . $task->task_type, 'local_student_monitor'));

        // Priority badge.
        $priorityclass = [
            'urgent' => 'badge-danger',
            'high' => 'badge-warning',
            'normal' => 'badge-info',
            'low' => 'badge-secondary'
        ][$task->priority] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', get_string('priority_' . $task->priority, 'local_student_monitor'),
            ['class' => 'badge ' . $priorityclass]);
        echo html_writer::end_tag('td');

        // Risk level badge.
        $riskclass = [
            'CRITICAL' => 'badge-danger',
            'HIGH' => 'badge-warning',
            'MEDIUM' => 'badge-info',
            'LOW' => 'badge-success'
        ][$task->risk_level] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', $task->risk_level, ['class' => 'badge ' . $riskclass]);
        echo html_writer::tag('br');
        echo html_writer::tag('small', get_string('inactivitydays', 'local_student_monitor') . ': ' . $task->inactivity_days,
            ['class' => 'text-muted']);
        echo html_writer::end_tag('td');

        // Due date.
        echo html_writer::start_tag('td');
        echo userdate($task->due_date, get_string('strftimedatetimeshort'));
        if ($isoverdue) {
            echo html_writer::tag('br');
            echo html_writer::tag('small', get_string('overdue', 'local_student_monitor'), ['class' => 'text-danger font-weight-bold']);
        }
        echo html_writer::end_tag('td');

        // Status.
        $statusclass = [
            'pending' => 'badge-warning',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success'
        ][$task->status] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', get_string('status_' . $task->status, 'local_student_monitor'),
            ['class' => 'badge ' . $statusclass]);
        echo html_writer::end_tag('td');

        // Actions.
        echo html_writer::start_tag('td');

        if ($task->status != 'completed') {
            // Mark as in progress.
            if ($task->status == 'pending') {
                $progressurl = new moodle_url($PAGE->url, [
                    'action' => 'complete',
                    'taskid' => $task->id,
                    'status' => 'in_progress',
                    'sesskey' => sesskey()
                ]);
                echo html_writer::link($progressurl, get_string('startwork', 'local_student_monitor'),
                    ['class' => 'btn btn-sm btn-primary']);
            }

            // Mark as complete.
            $completeurl = new moodle_url($PAGE->url, [
                'action' => 'complete',
                'taskid' => $task->id,
                'sesskey' => sesskey()
            ]);
            echo html_writer::link($completeurl, get_string('markcomplete', 'local_student_monitor'),
                ['class' => 'btn btn-sm btn-success ml-1']);
        }

        // View student details.
        $detailsurl = new moodle_url('/local/student_monitor/student_detail.php', ['userid' => $task->student_id]);
        echo html_writer::link($detailsurl, get_string('viewdetails', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-info ml-1']);

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
