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
 * Create manual alert page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:sendmanual', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/create_alert.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('createalert', 'local_student_monitor'));
$PAGE->set_heading(get_string('createalert', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Create form.
$mform = new \local_student_monitor\form\manual_alert_form();

// Check if we're returning from preview with session data.
$sessionkey = 'local_student_monitor_preview_data';
if (isset($SESSION->$sessionkey)) {
    // Restore data from session.
    $mform->set_data($SESSION->$sessionkey);
}

// Form processing.
if ($mform->is_cancelled()) {
    // Clear session data if exists.
    if (isset($SESSION->$sessionkey)) {
        unset($SESSION->$sessionkey);
    }
    redirect(new moodle_url('/local/student_monitor/dashboard.php'));
} else if ($data = $mform->get_data()) {
    // Check which button was pressed.
    if (isset($data->submitbutton)) {
        // Preview button was pressed - save data to session and redirect to preview.
        $SESSION->$sessionkey = $data;
        redirect(new moodle_url('/local/student_monitor/preview_alert.php'));
    } else {
        // Send button was pressed - create and send alert.
        $alertmanager = new \local_student_monitor\manager\alert_manager();
        $result = $alertmanager->create_manual_alert($data);

        // Clear session data if exists.
        if (isset($SESSION->$sessionkey)) {
            unset($SESSION->$sessionkey);
        }

        if ($result['count'] > 0) {
            // Build success message with send statistics.
            $message = get_string('alertcreated', 'local_student_monitor') . ' ';
            if ($result['success'] > 0) {
                $message .= get_string('alertssent', 'local_student_monitor', $result['success']);
            }
            if ($result['failed'] > 0) {
                $message .= ' - ' . get_string('alertsfailed', 'local_student_monitor', $result['failed']);
            }

            $notifytype = ($result['failed'] > 0) ? \core\output\notification::NOTIFY_WARNING : \core\output\notification::NOTIFY_SUCCESS;

            redirect(
                new moodle_url('/local/student_monitor/dashboard.php'),
                $message,
                null,
                $notifytype
            );
        } else {
            redirect(
                new moodle_url('/local/student_monitor/create_alert.php'),
                get_string('error_creating_alert', 'local_student_monitor'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }
    }
}

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('p', get_string('createalertdesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

// Display form.
$mform->display();

echo $OUTPUT->footer();
