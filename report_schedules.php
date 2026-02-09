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
 * Report schedules management page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managesettings', context_system::instance());

$action = optional_param('action', 'list', PARAM_ALPHA);
$scheduleid = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/report_schedules.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('reportschedules', 'local_student_monitor'));
$PAGE->set_heading(get_string('reportschedules', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$scheduler = new \local_student_monitor\manager\report_scheduler();

// Handle actions.
if ($action == 'delete' && $scheduleid && confirm_sesskey()) {
    $scheduler->delete_schedule($scheduleid);
    redirect($PAGE->url, get_string('scheduledeleted', 'local_student_monitor'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action == 'toggle' && $scheduleid && confirm_sesskey()) {
    $scheduler->toggle_schedule($scheduleid);
    redirect($PAGE->url);
}

$schedules = $scheduler->get_all_schedules();

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('reportschedules', 'local_student_monitor'));

// Create new schedule button.
echo html_writer::link(
    new moodle_url('/local/student_monitor/create_schedule.php'),
    get_string('createnewschedule', 'local_student_monitor'),
    ['class' => 'btn btn-primary mb-3']
);

// Schedules list.
if (empty($schedules)) {
    echo html_writer::div(
        get_string('noschedules', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('reporttype', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('frequency', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('format', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('recipients', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('lastrun', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('nextrun', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('status', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($schedules as $schedule) {
        $recipients = json_decode($schedule->recipients, true) ?? [];

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('report_' . $schedule->report_type, 'local_student_monitor'));
        echo html_writer::tag('td', get_string('freq_' . $schedule->frequency, 'local_student_monitor'));
        echo html_writer::tag('td', strtoupper($schedule->format));
        echo html_writer::tag('td', count($recipients) . ' ' . get_string('recipients', 'local_student_monitor'));
        echo html_writer::tag('td', $schedule->last_run > 0 ? userdate($schedule->last_run, '%Y-%m-%d %H:%M') : '-');
        echo html_writer::tag('td', userdate($schedule->next_run, '%Y-%m-%d %H:%M'));

        $statusclass = $schedule->enabled ? 'badge-success' : 'badge-secondary';
        $statustext = $schedule->enabled ? get_string('enabled', 'local_student_monitor') : get_string('disabled', 'local_student_monitor');
        echo html_writer::tag('td', html_writer::tag('span', $statustext, ['class' => 'badge ' . $statusclass]));

        // Actions.
        echo html_writer::start_tag('td');

        $toggleurl = new moodle_url($PAGE->url, ['action' => 'toggle', 'id' => $schedule->id, 'sesskey' => sesskey()]);
        echo html_writer::link($toggleurl, $schedule->enabled ? get_string('disable', 'local_student_monitor') : get_string('enable', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-secondary mr-1']);

        $deleteurl = new moodle_url($PAGE->url, ['action' => 'delete', 'id' => $schedule->id, 'sesskey' => sesskey()]);
        echo html_writer::link($deleteurl, get_string('delete', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-danger', 'onclick' => 'return confirm("' . get_string('confirmdelete', 'local_student_monitor') . '");']);

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
