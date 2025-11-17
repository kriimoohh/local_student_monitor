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
 * Student notification preferences page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_user::instance($USER->id);

$PAGE->set_url(new moodle_url('/local/student_monitor/preferences.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('notificationpreferences', 'local_student_monitor'));
$PAGE->set_heading(get_string('notificationpreferences', 'local_student_monitor'));
$PAGE->set_pagelayout('standard');

// Load user preferences.
$preferences = [
    'email' => get_user_preferences('local_student_monitor_channel_email', 1, $USER->id),
    'moodle' => get_user_preferences('local_student_monitor_channel_moodle', 1, $USER->id),
    'sms' => get_user_preferences('local_student_monitor_channel_sms', 0, $USER->id),
    'whatsapp' => get_user_preferences('local_student_monitor_channel_whatsapp', 0, $USER->id),
];

// Form submission.
if (optional_param('save', false, PARAM_BOOL) && confirm_sesskey()) {
    // Update preferences.
    set_user_preference('local_student_monitor_channel_email', optional_param('channel_email', 0, PARAM_INT), $USER->id);
    set_user_preference('local_student_monitor_channel_moodle', optional_param('channel_moodle', 0, PARAM_INT), $USER->id);
    set_user_preference('local_student_monitor_channel_sms', optional_param('channel_sms', 0, PARAM_INT), $USER->id);
    set_user_preference('local_student_monitor_channel_whatsapp', optional_param('channel_whatsapp', 0, PARAM_INT), $USER->id);

    redirect(
        $PAGE->url,
        get_string('preferencessaved', 'local_student_monitor'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('notificationpreferences', 'local_student_monitor'));

echo html_writer::tag('p', get_string('preferencesdesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

// Preferences form.
echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url,
    'class' => 'mform',
]);

echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'save', 'value' => '1']);

// Channels section.
echo html_writer::start_div('card mb-3');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('channels', 'local_student_monitor'), ['class' => 'card-title']);

echo html_writer::tag('p', get_string('selectchannels', 'local_student_monitor'));

// Email.
echo html_writer::start_div('form-check mb-2');
echo html_writer::checkbox('channel_email', 1, $preferences['email'], get_string('channelemail', 'local_student_monitor'), [
    'class' => 'form-check-input',
    'id' => 'channel_email',
]);
echo html_writer::tag('label', get_string('channelemail', 'local_student_monitor'), [
    'for' => 'channel_email',
    'class' => 'form-check-label',
]);
echo html_writer::tag('small', get_string('channelemail_desc', 'local_student_monitor'), [
    'class' => 'd-block text-muted'
]);
echo html_writer::end_div();

// Moodle notifications.
echo html_writer::start_div('form-check mb-2');
echo html_writer::checkbox('channel_moodle', 1, $preferences['moodle'], get_string('channelmoodle', 'local_student_monitor'), [
    'class' => 'form-check-input',
    'id' => 'channel_moodle',
]);
echo html_writer::tag('label', get_string('channelmoodle', 'local_student_monitor'), [
    'for' => 'channel_moodle',
    'class' => 'form-check-label',
]);
echo html_writer::tag('small', get_string('channelmoodle_desc', 'local_student_monitor'), [
    'class' => 'd-block text-muted'
]);
echo html_writer::end_div();

// SMS (if enabled).
if (get_config('local_student_monitor', 'channel_sms')) {
    echo html_writer::start_div('form-check mb-2');
    echo html_writer::checkbox('channel_sms', 1, $preferences['sms'], get_string('channelsms', 'local_student_monitor'), [
        'class' => 'form-check-input',
        'id' => 'channel_sms',
    ]);
    echo html_writer::tag('label', get_string('channelsms', 'local_student_monitor'), [
        'for' => 'channel_sms',
        'class' => 'form-check-label',
    ]);
    echo html_writer::tag('small', get_string('channelsms_desc', 'local_student_monitor'), [
        'class' => 'd-block text-muted'
    ]);
    echo html_writer::end_div();

    // Display user's phone number if available.
    if (!empty($USER->phone1)) {
        echo html_writer::tag('small', get_string('yourphone', 'local_student_monitor') . ': ' . $USER->phone1, [
            'class' => 'text-muted ml-4 d-block mb-2'
        ]);
    }
}

// WhatsApp (if enabled).
if (get_config('local_student_monitor', 'channel_whatsapp')) {
    echo html_writer::start_div('form-check mb-2');
    echo html_writer::checkbox('channel_whatsapp', 1, $preferences['whatsapp'], get_string('channelwhatsapp', 'local_student_monitor'), [
        'class' => 'form-check-input',
        'id' => 'channel_whatsapp',
    ]);
    echo html_writer::tag('label', get_string('channelwhatsapp', 'local_student_monitor'), [
        'for' => 'channel_whatsapp',
        'class' => 'form-check-label',
    ]);
    echo html_writer::tag('small', get_string('channelwhatsapp_desc', 'local_student_monitor'), [
        'class' => 'd-block text-muted'
    ]);
    echo html_writer::end_div();
}

echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Submit button.
echo html_writer::start_div('mt-3');
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('savechanges'),
    'class' => 'btn btn-primary',
]);

$cancelurl = new moodle_url('/user/preferences.php');
echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary ml-2']);
echo html_writer::end_div();

echo html_writer::end_tag('form');

// My notifications history.
echo html_writer::start_div('mt-4');
echo html_writer::tag('h3', get_string('mynotifications', 'local_student_monitor'));

$notifications = $DB->get_records('local_sm_notifications', ['userid' => $USER->id], 'timecreated DESC', '*', 0, 10);

if (!empty($notifications)) {
    $table = new html_table();
    $table->head = [
        get_string('type'),
        get_string('subject'),
        get_string('status'),
        get_string('date'),
    ];
    $table->attributes['class'] = 'table table-striped';

    foreach ($notifications as $notif) {
        $row = [
            local_student_monitor_get_notification_type_name($notif->type),
            $notif->subject,
            get_string('status_' . $notif->status, 'local_student_monitor'),
            userdate($notif->timecreated, get_string('strftimedatetime')),
        ];
        $table->data[] = $row;
    }

    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('nonotifications', 'local_student_monitor'), ['class' => 'alert alert-info']);
}

echo html_writer->end_div();

echo $OUTPUT->footer();
