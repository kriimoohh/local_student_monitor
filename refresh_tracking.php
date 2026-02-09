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
 * Refresh student tracking data.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:viewdashboard', $context);

// Confirm token for security.
$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/refresh_tracking.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('refreshtracking', 'local_student_monitor'));
$PAGE->set_heading(get_string('refreshtracking', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// If not confirmed, show confirmation page.
if (!$confirm || !confirm_sesskey()) {
    echo $OUTPUT->header();

    echo html_writer::div(
        html_writer::tag('h3', '🔄 ' . get_string('refreshtracking', 'local_student_monitor')),
        'mb-3'
    );

    echo html_writer::div(
        get_string('refreshtrackingconfirm', 'local_student_monitor'),
        'alert alert-info'
    );

    echo html_writer::div(
        get_string('refreshtrackinginfo', 'local_student_monitor'),
        'alert alert-warning'
    );

    $confirmurl = new moodle_url('/local/student_monitor/refresh_tracking.php', [
        'confirm' => 1,
        'sesskey' => sesskey()
    ]);

    $cancelurl = new moodle_url('/local/student_monitor/dashboard.php');

    echo html_writer::start_div('mt-3');
    echo html_writer::link($confirmurl, get_string('confirm'), ['class' => 'btn btn-primary mr-2']);
    echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_div();

    echo $OUTPUT->footer();
    exit;
}

// Perform the refresh.
raise_memory_limit(MEMORY_HUGE);
core_php_time_limit::raise(300); // 5 minutes max.

$tracker = new \local_student_monitor\manager\student_tracker();

// Get all active students.
$students = $DB->get_records_sql(
    "SELECT DISTINCT u.id, u.firstname, u.lastname,
            u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
       FROM {user} u
       JOIN {role_assignments} ra ON ra.userid = u.id
       JOIN {role} r ON r.id = ra.roleid
      WHERE r.shortname = 'student'
        AND u.deleted = 0
        AND u.suspended = 0"
);

$totalstudents = count($students);
$updated = 0;
$failed = 0;

// Update tracking for each student.
foreach ($students as $student) {
    try {
        // Update global tracking (courseid = null).
        if ($tracker->update_student_tracking($student->id, null)) {
            $updated++;
        } else {
            $failed++;
        }
    } catch (Exception $e) {
        $failed++;
        debugging('Failed to update tracking for user ' . $student->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
}

// Also trigger update for course-specific tracking for enrolled students.
$coursetracking = 0;
$courses = $DB->get_records_sql(
    "SELECT DISTINCT c.id
       FROM {course} c
       JOIN {enrol} e ON e.courseid = c.id
       JOIN {user_enrolments} ue ON ue.enrolid = e.id
      WHERE c.id > 1
        AND c.visible = 1"
);

foreach ($courses as $course) {
    // Get enrolled students in this course.
    $enrolledstudents = $DB->get_records_sql(
        "SELECT DISTINCT u.id
           FROM {user} u
           JOIN {user_enrolments} ue ON ue.userid = u.id
           JOIN {enrol} e ON e.id = ue.enrolid
           JOIN {role_assignments} ra ON ra.userid = u.id
           JOIN {context} ctx ON ctx.id = ra.contextid
           JOIN {role} r ON r.id = ra.roleid
          WHERE e.courseid = :courseid
            AND ctx.contextlevel = 50
            AND ctx.instanceid = :courseid2
            AND r.shortname = 'student'
            AND u.deleted = 0
            AND u.suspended = 0",
        ['courseid' => $course->id, 'courseid2' => $course->id]
    );

    foreach ($enrolledstudents as $student) {
        try {
            if ($tracker->update_student_tracking($student->id, $course->id)) {
                $coursetracking++;
            }
        } catch (Exception $e) {
            debugging('Failed to update course tracking for user ' . $student->id . ' in course ' . $course->id, DEBUG_DEVELOPER);
        }
    }
}

// Redirect back to dashboard with success message.
redirect(
    new moodle_url('/local/student_monitor/dashboard.php'),
    get_string('refreshtrackingsuccess', 'local_student_monitor', [
        'total' => $totalstudents,
        'updated' => $updated,
        'failed' => $failed,
        'courses' => $coursetracking
    ]),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
