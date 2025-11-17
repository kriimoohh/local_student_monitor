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
 * Student goals management page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$action = optional_param('action', 'view', PARAM_ALPHA);
$goalid = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/my_goals.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('mygoals', 'local_student_monitor'));
$PAGE->set_heading(get_string('mygoals', 'local_student_monitor'));
$PAGE->set_pagelayout('standard');

$progresstracker = new \local_student_monitor\manager\progress_tracker();

// Handle actions.
if ($action == 'create' && confirm_sesskey()) {
    $type = required_param('type', PARAM_ALPHA);
    $title = required_param('title', PARAM_TEXT);
    $description = optional_param('description', '', PARAM_TEXT);
    $targetvalue = required_param('target_value', PARAM_FLOAT);
    $deadline = required_param('deadline', PARAM_INT);

    $progresstracker->create_goal($USER->id, $type, $title, $description, $targetvalue, $deadline);

    redirect(new moodle_url('/local/student_monitor/my_goals.php'),
        get_string('goalcreated', 'local_student_monitor'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Get goals and statistics.
$activegoals = $progresstracker->get_user_goals($USER->id, \local_student_monitor\manager\progress_tracker::STATUS_ACTIVE);
$completedgoals = $progresstracker->get_user_goals($USER->id, \local_student_monitor\manager\progress_tracker::STATUS_COMPLETED);
$stats = $progresstracker->get_goal_statistics($USER->id);
$suggestions = $progresstracker->get_suggested_goals($USER->id);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('mygoals', 'local_student_monitor'));

// Statistics.
echo html_writer::start_div('row mb-4');

// Total goals.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-primary text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('totalgoals', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_goals, ['class' => 'display-4']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Active goals.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-info text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('activegoals', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->active_goals, ['class' => 'display-4']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Completed goals.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-success text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('completedgoals', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->completed_goals, ['class' => 'display-4']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Completion rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-warning text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('completionrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', round($stats->completion_rate) . '%', ['class' => 'display-4']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Suggested goals.
if (!empty($suggestions)) {
    echo html_writer::tag('h3', '💡 ' . get_string('suggestedgoals', 'local_student_monitor'), ['class' => 'mt-4']);

    echo html_writer::start_div('row mb-4');

    foreach ($suggestions as $suggestion) {
        echo html_writer::start_div('col-md-6');
        echo html_writer::start_div('card border-primary');
        echo html_writer::start_div('card-body');
        echo html_writer::tag('h5', $suggestion->title, ['class' => 'card-title']);
        echo html_writer::tag('p', $suggestion->description, ['class' => 'card-text']);

        // Create goal form.
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'create']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => $suggestion->type]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'title', 'value' => $suggestion->title]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'description', 'value' => $suggestion->description]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'target_value', 'value' => $suggestion->target_value]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deadline', 'value' => $suggestion->suggested_deadline]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

        echo html_writer::tag('button', get_string('createthisgoal', 'local_student_monitor'),
            ['type' => 'submit', 'class' => 'btn btn-primary btn-sm']);
        echo html_writer::end_tag('form');

        echo html_writer::end_div();
        echo html_writer::end_div();
        echo html_writer::end_div();
    }

    echo html_writer::end_div();
}

// Active goals.
echo html_writer::tag('h3', '🎯 ' . get_string('activegoals', 'local_student_monitor'), ['class' => 'mt-4']);

if (empty($activegoals)) {
    echo html_writer::div(
        get_string('noactivegoals', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    foreach ($activegoals as $goal) {
        $progress = $goal->target_value > 0 ? ($goal->current_value / $goal->target_value) * 100 : 0;
        $progress = min($progress, 100);

        $daysremaining = $goal->deadline > 0 ? ceil(($goal->deadline - time()) / (24 * 3600)) : null;

        echo html_writer::start_div('card mb-3');
        echo html_writer::start_div('card-body');

        echo html_writer::start_div('d-flex w-100 justify-content-between');
        echo html_writer::tag('h5', $goal->title, ['class' => 'mb-1']);

        if ($daysremaining !== null) {
            $deadlineclass = 'info';
            if ($daysremaining <= 3) {
                $deadlineclass = 'danger';
            } else if ($daysremaining <= 7) {
                $deadlineclass = 'warning';
            }

            echo html_writer::tag('span',
                $daysremaining . ' ' . get_string('daysremaining', 'local_student_monitor'),
                ['class' => 'badge badge-' . $deadlineclass]
            );
        }

        echo html_writer::end_div();

        if ($goal->description) {
            echo html_writer::tag('p', $goal->description, ['class' => 'mb-2 text-muted']);
        }

        echo html_writer::tag('div',
            get_string('progress', 'local_student_monitor') . ': ' .
            $goal->current_value . ' / ' . $goal->target_value,
            ['class' => 'mb-2']
        );

        echo html_writer::start_div('progress', ['style' => 'height: 25px;']);
        echo html_writer::div(
            round($progress) . '%',
            'progress-bar bg-success',
            ['style' => 'width: ' . $progress . '%', 'role' => 'progressbar']
        );
        echo html_writer::end_div();

        echo html_writer::end_div();
        echo html_writer::end_div();
    }
}

// Completed goals.
if (!empty($completedgoals)) {
    echo html_writer::tag('h3', '✅ ' . get_string('completedgoals', 'local_student_monitor'), ['class' => 'mt-4']);

    foreach (array_slice($completedgoals, 0, 5) as $goal) {
        echo html_writer::start_div('card mb-2');
        echo html_writer::start_div('card-body');

        echo html_writer::start_div('d-flex w-100 justify-content-between');
        echo html_writer::tag('h6', $goal->title, ['class' => 'mb-1']);
        echo html_writer::tag('span', '✓', ['class' => 'badge badge-success']);
        echo html_writer::end_div();

        if ($goal->timecompleted) {
            echo html_writer::tag('small',
                get_string('completedon', 'local_student_monitor', userdate($goal->timecompleted)),
                ['class' => 'text-muted']
            );
        }

        echo html_writer::end_div();
        echo html_writer::end_div();
    }
}

// Create custom goal button.
echo html_writer::tag('h3', get_string('createcustomgoal', 'local_student_monitor'), ['class' => 'mt-4']);
echo html_writer::div(
    get_string('customgoal_desc', 'local_student_monitor'),
    'alert alert-light mb-3'
);

// Simple form for custom goal.
echo html_writer::start_tag('form', ['method' => 'post', 'class' => 'card p-3']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'create']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => 'custom']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('goaltitle', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'title',
    'class' => 'form-control',
    'required' => 'required'
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('goaldescription', 'local_student_monitor'));
echo html_writer::tag('textarea', '', [
    'name' => 'description',
    'class' => 'form-control',
    'rows' => '3'
]);
echo html_writer::end_div();

echo html_writer::start_div('form-row');

echo html_writer::start_div('form-group col-md-6');
echo html_writer::tag('label', get_string('targetvalue', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'target_value',
    'class' => 'form-control',
    'required' => 'required',
    'min' => '1'
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group col-md-6');
echo html_writer::tag('label', get_string('deadline', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'date',
    'name' => 'deadline',
    'class' => 'form-control',
    'value' => date('Y-m-d', time() + (30 * 24 * 3600)), // Default 30 days from now
    'min' => date('Y-m-d'),
    'onchange' => 'this.setAttribute(\'value\', Math.floor(new Date(this.value).getTime()/1000))'
]);
echo html_writer::end_div();

echo html_writer::end_div();

echo html_writer::tag('button', get_string('creategoal', 'local_student_monitor'),
    ['type' => 'submit', 'class' => 'btn btn-primary']);

echo html_writer::end_tag('form');

// Back to dashboard.
$dashboardurl = new moodle_url('/local/student_monitor/student_dashboard.php');
echo html_writer::link($dashboardurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-4']);

echo $OUTPUT->footer();
