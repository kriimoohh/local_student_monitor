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
 * Preview alert before sending.
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

$PAGE->set_url(new moodle_url('/local/student_monitor/preview_alert.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('previewalert', 'local_student_monitor'));
$PAGE->set_heading(get_string('previewalert', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Get session data.
$sessionkey = 'local_student_monitor_preview_data';
if (!isset($SESSION->$sessionkey)) {
    redirect(new moodle_url('/local/student_monitor/create_alert.php'),
        get_string('error_no_preview_data', 'local_student_monitor'),
        null,
        \core\output\notification::NOTIFY_ERROR);
}

$data = $SESSION->$sessionkey;

// Handle form submission.
$action = optional_param('action', '', PARAM_ALPHA);
if ($action && confirm_sesskey()) {
    if ($action === 'send') {
        // Send the alert.
        $alertmanager = new \local_student_monitor\manager\alert_manager();
        $result = $alertmanager->create_manual_alert($data);

        // Clear session data.
        unset($SESSION->$sessionkey);

        if ($result['count'] > 0) {
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
    } else if ($action === 'back') {
        // Go back to edit form.
        redirect(new moodle_url('/local/student_monitor/create_alert.php'));
    }
}

// Get alert manager to retrieve recipients.
$alertmanager = new \local_student_monitor\manager\alert_manager();
$recipients = $alertmanager->get_recipients_for_preview($data);

// Get sender information.
$sender = $USER;

// Format description.
$description = $data->description['text'];

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('h2', '👁️ ' . get_string('previewalert', 'local_student_monitor'), ['class' => 'sm-page-title']);

echo html_writer::tag('p', get_string('previewalertdesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

// Preview card.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');

// Alert type.
echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('alerttype', 'local_student_monitor') . ': ');
echo get_string('alert_' . $data->alerttype, 'local_student_monitor');
echo html_writer::end_div();

// Subject/Title.
echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('subject', 'local_student_monitor') . ': ');
echo html_writer::tag('span', s($data->title), ['class' => 'h5']);
echo html_writer::end_div();

// Sender.
echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('sender', 'local_student_monitor') . ': ');
echo s(fullname($sender) . ' (' . $sender->email . ')');
echo html_writer::end_div();

// Event date (if applicable).
if (!empty($data->eventdate)) {
    echo html_writer::start_div('mb-3');
    echo html_writer::tag('strong', get_string('eventdate', 'local_student_monitor') . ': ');
    echo userdate($data->eventdate, get_string('strftimedatetimeshort'));
    echo html_writer::end_div();
}

// Location (if applicable).
if (!empty($data->location)) {
    echo html_writer::start_div('mb-3');
    echo html_writer::tag('strong', get_string('location') . ': ');
    echo s($data->location);
    echo html_writer::end_div();
}

// Description/Message.
echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('message', 'local_student_monitor') . ': ');
echo html_writer::start_div('card mt-2');
echo html_writer::start_div('card-body');
echo format_text($description, FORMAT_HTML);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Channels.
$channels = [];
if (!empty($data->channel_email)) {
    $channels[] = get_string('channelemail', 'local_student_monitor');
}
if (!empty($data->channel_moodle)) {
    $channels[] = get_string('channelmoodle', 'local_student_monitor');
}
if (!empty($data->channel_sms)) {
    $channels[] = get_string('channelsms', 'local_student_monitor');
}
if (!empty($data->channel_whatsapp)) {
    $channels[] = get_string('channelwhatsapp', 'local_student_monitor');
}

echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('channels', 'local_student_monitor') . ': ');
echo implode(', ', $channels);
echo html_writer::end_div();

// Reminders.
$reminders = [];
if (!empty($data->reminder_7days)) {
    $reminders[] = get_string('reminder7days', 'local_student_monitor');
}
if (!empty($data->reminder_3days)) {
    $reminders[] = get_string('reminder3days', 'local_student_monitor');
}
if (!empty($data->reminder_1day)) {
    $reminders[] = get_string('reminder1day', 'local_student_monitor');
}

if (!empty($reminders)) {
    echo html_writer::start_div('mb-3');
    echo html_writer::tag('strong', get_string('reminders', 'local_student_monitor') . ': ');
    echo implode(', ', $reminders);
    echo html_writer::end_div();
}

// Recipients.
echo html_writer::start_div('mb-3');
echo html_writer::tag('strong', get_string('recipients', 'local_student_monitor') . ': ');
echo html_writer::tag('span', get_string('recipients_' . $data->recipients, 'local_student_monitor'));
echo ' (' . count($recipients) . ' ' . get_string('students', 'local_student_monitor') . ')';

// Show first 10 recipients.
if (count($recipients) > 0) {
    echo html_writer::start_div('card mt-2');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('strong', get_string('recipientslist', 'local_student_monitor') . ':');
    echo html_writer::start_tag('ul', ['class' => 'list-unstyled mt-2']);

    $maxdisplay = min(10, count($recipients));
    for ($i = 0; $i < $maxdisplay; $i++) {
        $recipient = $recipients[$i];
        echo html_writer::tag('li', fullname($recipient) . ' (' . $recipient->email . ')');
    }

    if (count($recipients) > 10) {
        echo html_writer::tag('li', '... ' . get_string('andmore', 'local_student_monitor', count($recipients) - 10),
            ['class' => 'text-muted']);
    }

    echo html_writer::end_tag('ul');
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div();

echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Action buttons.
echo html_writer::start_div('mt-4');

// Back button.
$backurl = new moodle_url($PAGE->url, ['action' => 'back', 'sesskey' => sesskey()]);
echo html_writer::link($backurl, '← ' . get_string('backtoedit', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mr-2']);

// Send button.
$sendurl = new moodle_url($PAGE->url, ['action' => 'send', 'sesskey' => sesskey()]);
echo html_writer::link($sendurl, '📧 ' . get_string('sendalert', 'local_student_monitor'),
    ['class' => 'btn btn-primary', 'onclick' => 'return confirm("' . get_string('confirmsendalert', 'local_student_monitor') . '")']);

echo html_writer::end_div();

echo $OUTPUT->footer();
