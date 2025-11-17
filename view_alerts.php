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
 * View alerts history page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:viewreports', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/view_alerts.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('viewalerts', 'local_student_monitor'));
$PAGE->set_heading(get_string('viewalerts', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Get alert manager.
$alertmanager = new \local_student_monitor\manager\alert_manager();

// Get recent alerts.
$recentalerts = $alertmanager->get_recent_alerts(50);

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('h3', get_string('recentalerts', 'local_student_monitor'));

// Display alerts table.
if (!empty($recentalerts)) {
    $table = new html_table();
    $table->head = [
        get_string('title', 'local_student_monitor'),
        get_string('sentby', 'local_student_monitor'),
        get_string('recipients', 'local_student_monitor'),
        get_string('timecreated', 'local_student_monitor'),
    ];

    $table->attributes['class'] = 'table table-striped';

    foreach ($recentalerts as $alert) {
        $row = [
            $alert->subject,
            fullname($alert),
            $alert->recipient_count,
            userdate($alert->timecreated, get_string('strftimedatetime')),
        ];

        $table->data[] = $row;
    }

    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('noalerts', 'local_student_monitor'), ['class' => 'alert alert-info']);
}

// Back button.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('back'), ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
