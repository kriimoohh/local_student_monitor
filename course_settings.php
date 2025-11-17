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
 * Course-specific Student Monitor settings page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$courseid = required_param('id', PARAM_INT);

require_login($courseid);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_capability('local/student_monitor:managesettings', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/course_settings.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('coursesettings', 'local_student_monitor'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');

// Load existing settings.
$existingsettings = $DB->get_records_menu('local_sm_config', ['courseid' => $courseid], '', 'config_key, config_value');

// Create form.
$mform = new \local_student_monitor\form\course_settings_form(null, ['course' => $course]);

// Set current data.
if (!empty($existingsettings)) {
    $data = new stdClass();
    $data->courseid = $courseid;
    foreach ($existingsettings as $key => $value) {
        $data->$key = $value;
    }
    $mform->set_data($data);
}

// Form processing.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $mform->get_data()) {
    // Save settings to database.
    $now = time();

    // Delete existing settings for this course.
    $DB->delete_records('local_sm_config', ['courseid' => $courseid]);

    // Save new settings.
    $settings = [
        'enabled',
        'notify_new_content',
        'activity_assign',
        'activity_quiz',
        'activity_forum',
        'activity_resource',
        'activity_url',
        'activity_page',
        'assignment_reminders',
        'reminder_days_custom',
        'monitor_inactivity',
        'inactivity_threshold_custom',
        'default_supervisor',
        'teacher_digest',
        'digest_frequency',
    ];

    foreach ($settings as $setting) {
        if (isset($data->$setting)) {
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->config_type = 'course';
            $record->config_key = $setting;
            $record->config_value = $data->$setting;
            $record->enabled = 1;
            $record->timecreated = $now;
            $record->timemodified = $now;

            $DB->insert_record('local_sm_config', $record);
        }
    }

    // Log the action.
    $event = \core\event\course_updated::create([
        'objectid' => $courseid,
        'context' => $context,
        'other' => ['fullname' => $course->fullname]
    ]);
    $event->trigger();

    redirect(
        new moodle_url('/local/student_monitor/course_settings.php', ['id' => $courseid]),
        get_string('settingssaved', 'local_student_monitor'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('coursesettings', 'local_student_monitor'));

echo html_writer::tag('p', get_string('coursesettingsdesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

// Display form.
$mform->display();

// Back button.
$backurl = new moodle_url('/course/view.php', ['id' => $courseid]);
echo html_writer::link($backurl, get_string('back'), ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
