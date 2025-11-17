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
 * Manual alert form.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating manual alerts.
 */
class manual_alert_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;

        // Alert type.
        $mform->addElement('select', 'alerttype', get_string('alerttype', 'local_student_monitor'), [
            'exam' => get_string('alert_exam', 'local_student_monitor'),
            'assignment' => get_string('alert_assignment', 'local_student_monitor'),
            'announcement' => get_string('alert_announcement', 'local_student_monitor'),
            'event' => get_string('alert_event', 'local_student_monitor'),
        ]);
        $mform->addRule('alerttype', null, 'required');

        // Title.
        $mform->addElement('text', 'title', get_string('title', 'local_student_monitor'), ['size' => 60]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required');

        // Event date/time.
        $mform->addElement('date_time_selector', 'eventdate', get_string('eventdate', 'local_student_monitor'), [
            'optional' => true,
        ]);
        $mform->addHelpButton('eventdate', 'eventdate', 'local_student_monitor');

        // Location (optional).
        $mform->addElement('text', 'location', get_string('location'), ['size' => 60]);
        $mform->setType('location', PARAM_TEXT);

        // Description.
        $mform->addElement('editor', 'description', get_string('description', 'local_student_monitor'), [
            'rows' => 10,
        ]);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required');

        // Recipients section.
        $mform->addElement('header', 'recipientshdr', get_string('recipients', 'local_student_monitor'));

        // Recipient type.
        $mform->addElement('select', 'recipients', get_string('recipients', 'local_student_monitor'), [
            'all_students' => get_string('allstudents', 'local_student_monitor'),
            'all_course' => get_string('recipients_all_course', 'local_student_monitor'),
            'group' => get_string('recipients_group', 'local_student_monitor'),
            'manual' => get_string('recipients_manual', 'local_student_monitor'),
        ]);
        $mform->addRule('recipients', null, 'required');

        // Course selection (for all_course and group recipients).
        $courses = $this->get_courses();
        $mform->addElement('select', 'courseid', get_string('course'), $courses);
        $mform->hideIf('courseid', 'recipients', 'eq', 'all_students');
        $mform->hideIf('courseid', 'recipients', 'eq', 'manual');

        // Group selection.
        $mform->addElement('select', 'groupid', get_string('group'), []);
        $mform->hideIf('groupid', 'recipients', 'neq', 'group');

        // Manual user selection (hidden field, populated by JavaScript).
        $mform->addElement('hidden', 'selectedusers');
        $mform->setType('selectedusers', PARAM_TEXT);

        // Channels section.
        $mform->addElement('header', 'channelshdr', get_string('channels', 'local_student_monitor'));

        // Email channel.
        $mform->addElement('advcheckbox', 'channel_email', get_string('channelemail', 'local_student_monitor'));
        $mform->setDefault('channel_email', 1);

        // Moodle notification channel.
        $mform->addElement('advcheckbox', 'channel_moodle', get_string('channelmoodle', 'local_student_monitor'));
        $mform->setDefault('channel_moodle', 1);

        // SMS channel (if enabled).
        if (get_config('local_student_monitor', 'channel_sms')) {
            $mform->addElement('advcheckbox', 'channel_sms', get_string('channelsms', 'local_student_monitor'));
        }

        // WhatsApp channel (if enabled).
        if (get_config('local_student_monitor', 'channel_whatsapp')) {
            $mform->addElement('advcheckbox', 'channel_whatsapp', get_string('channelwhatsapp', 'local_student_monitor'));
        }

        // Reminders section.
        $mform->addElement('header', 'remindershdr', get_string('reminders', 'local_student_monitor'));

        $mform->addElement('advcheckbox', 'reminder_7days', get_string('reminder7days', 'local_student_monitor'));
        $mform->addElement('advcheckbox', 'reminder_3days', get_string('reminder3days', 'local_student_monitor'));
        $mform->addElement('advcheckbox', 'reminder_1day', get_string('reminder1day', 'local_student_monitor'));

        $mform->addHelpButton('remindershdr', 'reminders', 'local_student_monitor');

        // Action buttons.
        $this->add_action_buttons(true, get_string('sendalert', 'local_student_monitor'));
    }

    /**
     * Form validation.
     *
     * @param array $data Form data
     * @param array $files Form files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate recipients.
        if ($data['recipients'] == 'all_course' && empty($data['courseid'])) {
            $errors['courseid'] = get_string('required');
        }

        if ($data['recipients'] == 'group' && empty($data['groupid'])) {
            $errors['groupid'] = get_string('required');
        }

        if ($data['recipients'] == 'manual' && empty($data['selectedusers'])) {
            $errors['selectedusers'] = get_string('selectusers', 'local_student_monitor');
        }

        // Validate at least one channel is selected.
        $channels = ['channel_email', 'channel_moodle', 'channel_sms', 'channel_whatsapp'];
        $hasChannel = false;
        foreach ($channels as $channel) {
            if (!empty($data[$channel])) {
                $hasChannel = true;
                break;
            }
        }

        if (!$hasChannel) {
            $errors['channel_email'] = get_string('selectatleastonechannel', 'local_student_monitor');
        }

        return $errors;
    }

    /**
     * Get available courses.
     *
     * @return array Array of courses
     */
    protected function get_courses() {
        global $DB;

        $courses = [0 => get_string('choosedots')];

        $allcourses = $DB->get_records('course', null, 'fullname', 'id, fullname');
        foreach ($allcourses as $course) {
            if ($course->id == SITEID) {
                continue;
            }
            $courses[$course->id] = $course->fullname;
        }

        return $courses;
    }
}
