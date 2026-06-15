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
 * Configure automatic alerts page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:managesettings', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/configure_automatic_alerts.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('configureautomaticalerts', 'local_student_monitor'));
$PAGE->set_heading(get_string('configureautomaticalerts', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Handle form submission.
$action = optional_param('action', '', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);

if ($action && confirm_sesskey()) {
    switch ($action) {
        case 'enable':
            set_config('automatic_alerts_enabled', 1, 'local_student_monitor');
            redirect($PAGE->url, get_string('automaticalertsenabled', 'local_student_monitor'),
                null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        case 'disable':
            if ($confirm) {
                set_config('automatic_alerts_enabled', 0, 'local_student_monitor');
                redirect($PAGE->url, get_string('automaticalertsdisabled', 'local_student_monitor'),
                    null, \core\output\notification::NOTIFY_WARNING);
            } else {
                // Show confirmation.
                echo $OUTPUT->header();
                echo $OUTPUT->confirm(
                    get_string('confirmdisableautomaticalerts', 'local_student_monitor'),
                    new moodle_url($PAGE->url, ['action' => 'disable', 'confirm' => 1, 'sesskey' => sesskey()]),
                    $PAGE->url
                );
                echo $OUTPUT->footer();
                exit;
            }
            break;

        case 'save_thresholds':
            $threshold1 = optional_param('threshold1', 3, PARAM_INT);
            $threshold2 = optional_param('threshold2', 7, PARAM_INT);
            $threshold3 = optional_param('threshold3', 14, PARAM_INT);

            set_config('inactivity_threshold_1', $threshold1, 'local_student_monitor');
            set_config('inactivity_threshold_2', $threshold2, 'local_student_monitor');
            set_config('inactivity_threshold_3', $threshold3, 'local_student_monitor');

            redirect($PAGE->url, get_string('thresholdssaved', 'local_student_monitor'),
                null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        case 'save_channels':
            $enableemail = optional_param('enable_email', 0, PARAM_INT);
            $enablemoodle = optional_param('enable_moodle', 0, PARAM_INT);
            $enablesms = optional_param('enable_sms', 0, PARAM_INT);
            $enablewhatsapp = optional_param('enable_whatsapp', 0, PARAM_INT);

            set_config('automatic_alerts_channel_email', $enableemail, 'local_student_monitor');
            set_config('automatic_alerts_channel_moodle', $enablemoodle, 'local_student_monitor');
            set_config('automatic_alerts_channel_sms', $enablesms, 'local_student_monitor');
            set_config('automatic_alerts_channel_whatsapp', $enablewhatsapp, 'local_student_monitor');

            redirect($PAGE->url, get_string('channelssaved', 'local_student_monitor'),
                null, \core\output\notification::NOTIFY_SUCCESS);
            break;
    }
}

// Get current configuration.
$enabled = get_config('local_student_monitor', 'automatic_alerts_enabled');
$threshold1 = get_config('local_student_monitor', 'inactivity_threshold_1') ?: 3;
$threshold2 = get_config('local_student_monitor', 'inactivity_threshold_2') ?: 7;
$threshold3 = get_config('local_student_monitor', 'inactivity_threshold_3') ?: 14;

$channelemail = get_config('local_student_monitor', 'automatic_alerts_channel_email');
$channelmoodle = get_config('local_student_monitor', 'automatic_alerts_channel_moodle');
$channelsms = get_config('local_student_monitor', 'automatic_alerts_channel_sms');
$channelwhatsapp = get_config('local_student_monitor', 'automatic_alerts_channel_whatsapp');

// Set defaults if not configured.
if ($channelemail === false) {
    $channelemail = 1;
}
if ($channelmoodle === false) {
    $channelmoodle = 1;
}

// Output starts here.
echo $OUTPUT->header();

echo html_writer::tag('h2', '⚙️ ' . get_string('configureautomaticalerts', 'local_student_monitor'), ['class' => 'sm-page-title']);

echo html_writer::tag('p', get_string('configureautomaticalertsdesc', 'local_student_monitor'),
    ['class' => 'alert alert-info']);

// Status Card.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('currentstatus', 'local_student_monitor'), ['class' => 'card-title']);

if ($enabled) {
    echo html_writer::start_div('alert alert-success');
    echo html_writer::tag('strong', '✅ ' . get_string('automaticalertsenabled', 'local_student_monitor'));
    echo html_writer::tag('p', get_string('automaticalertsenabledinfo', 'local_student_monitor'));
    echo html_writer::end_div();

    $disableurl = new moodle_url($PAGE->url, ['action' => 'disable', 'sesskey' => sesskey()]);
    echo html_writer::link($disableurl, get_string('disableautomaticalerts', 'local_student_monitor'),
        ['class' => 'btn btn-warning']);
} else {
    echo html_writer::start_div('alert alert-warning');
    echo html_writer::tag('strong', '⚠️ ' . get_string('automaticalertsdisabled', 'local_student_monitor'));
    echo html_writer::tag('p', get_string('automaticalertsdisabledinfo', 'local_student_monitor'));
    echo html_writer::end_div();

    $enableurl = new moodle_url($PAGE->url, ['action' => 'enable', 'sesskey' => sesskey()]);
    echo html_writer::link($enableurl, get_string('enableautomaticalerts', 'local_student_monitor'),
        ['class' => 'btn btn-success']);
}

echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Thresholds Configuration.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('inactivitythresholds', 'local_student_monitor'), ['class' => 'card-title']);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save_thresholds']);

echo html_writer::start_div('row');

// Level 1.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('inactivitythreshold1', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'threshold1',
    'value' => $threshold1,
    'class' => 'form-control',
    'min' => 1,
    'required' => 'required',
]);
echo html_writer::tag('small', get_string('inactivitythreshold1_desc', 'local_student_monitor'),
    ['class' => 'form-text text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();

// Level 2.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('inactivitythreshold2', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'threshold2',
    'value' => $threshold2,
    'class' => 'form-control',
    'min' => 1,
    'required' => 'required',
]);
echo html_writer::tag('small', get_string('inactivitythreshold2_desc', 'local_student_monitor'),
    ['class' => 'form-text text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();

// Level 3.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('inactivitythreshold3', 'local_student_monitor'));
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'name' => 'threshold3',
    'value' => $threshold3,
    'class' => 'form-control',
    'min' => 1,
    'required' => 'required',
]);
echo html_writer::tag('small', get_string('inactivitythreshold3_desc', 'local_student_monitor'),
    ['class' => 'form-text text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // row.

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('savethresholds', 'local_student_monitor'),
    'class' => 'btn btn-primary',
]);

echo html_writer::end_tag('form');

echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Notification Channels Configuration.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('notificationchannels', 'local_student_monitor'), ['class' => 'card-title']);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save_channels']);

// Email channel.
echo html_writer::start_div('form-check mb-2');
echo html_writer::empty_tag('input', [
    'type' => 'checkbox',
    'name' => 'enable_email',
    'id' => 'enable_email',
    'value' => 1,
    'checked' => $channelemail ? 'checked' : null,
    'class' => 'form-check-input',
]);
echo html_writer::tag('label', get_string('channelemail', 'local_student_monitor'),
    ['for' => 'enable_email', 'class' => 'form-check-label']);
echo html_writer::end_div();

// Moodle notification channel.
echo html_writer::start_div('form-check mb-2');
echo html_writer::empty_tag('input', [
    'type' => 'checkbox',
    'name' => 'enable_moodle',
    'id' => 'enable_moodle',
    'value' => 1,
    'checked' => $channelmoodle ? 'checked' : null,
    'class' => 'form-check-input',
]);
echo html_writer::tag('label', get_string('channelmoodle', 'local_student_monitor'),
    ['for' => 'enable_moodle', 'class' => 'form-check-label']);
echo html_writer::end_div();

// SMS channel (if enabled globally).
if (get_config('local_student_monitor', 'channel_sms')) {
    echo html_writer::start_div('form-check mb-2');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox',
        'name' => 'enable_sms',
        'id' => 'enable_sms',
        'value' => 1,
        'checked' => $channelsms ? 'checked' : null,
        'class' => 'form-check-input',
    ]);
    echo html_writer::tag('label', get_string('channelsms', 'local_student_monitor'),
        ['for' => 'enable_sms', 'class' => 'form-check-label']);
    echo html_writer::end_div();
}

// WhatsApp channel (if enabled globally).
if (get_config('local_student_monitor', 'channel_whatsapp')) {
    echo html_writer::start_div('form-check mb-2');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox',
        'name' => 'enable_whatsapp',
        'id' => 'enable_whatsapp',
        'value' => 1,
        'checked' => $channelwhatsapp ? 'checked' : null,
        'class' => 'form-check-input',
    ]);
    echo html_writer::tag('label', get_string('channelwhatsapp', 'local_student_monitor'),
        ['for' => 'enable_whatsapp', 'class' => 'form-check-label']);
    echo html_writer::end_div();
}

echo html_writer::tag('p', get_string('channelsconfigurationdesc', 'local_student_monitor'),
    ['class' => 'form-text text-muted mt-3']);

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('savechannels', 'local_student_monitor'),
    'class' => 'btn btn-primary mt-2',
]);

echo html_writer::end_tag('form');

echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Back to dashboard.
echo html_writer::start_div('mb-4');
$dashboardurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($dashboardurl, '← ' . get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary']);
echo html_writer::end_div();

echo $OUTPUT->footer();
