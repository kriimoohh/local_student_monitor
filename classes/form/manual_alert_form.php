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
            'by_inactivity_level' => get_string('recipients_by_inactivity_level', 'local_student_monitor'),
            'category' => get_string('recipients_category', 'local_student_monitor'),
            'all_course' => get_string('recipients_all_course', 'local_student_monitor'),
            'group' => get_string('recipients_group', 'local_student_monitor'),
            'manual' => get_string('recipients_manual', 'local_student_monitor'),
            'csv' => get_string('recipients_csv', 'local_student_monitor'),
        ]);
        $mform->addRule('recipients', null, 'required');

        // Inactivity level selection (for by_inactivity_level recipients).
        $tracker = new \local_student_monitor\manager\student_tracker();
        $stats = $tracker->get_statistics();

        $inactivityoptions = [
            'inactivity_level1' => get_string('inactivity_level1', 'local_student_monitor') . ' (' .
                get_string('inactivitydays_3plus', 'local_student_monitor') . ')',
            'inactivity_level2' => get_string('inactivity_level2', 'local_student_monitor') . ' (' .
                get_string('inactivitydays_7plus', 'local_student_monitor') . ')',
            'inactivity_level3' => get_string('inactivity_level3', 'local_student_monitor') . ' (' .
                get_string('inactivitydays_14plus', 'local_student_monitor') . ')',
            'risk_critique' => get_string('risk_critique', 'local_student_monitor') . ' (' . $stats->critique . ' ' .
                get_string('students', 'local_student_monitor') . ')',
            'risk_eleve' => get_string('risk_eleve', 'local_student_monitor') . ' (' . $stats->eleve . ' ' .
                get_string('students', 'local_student_monitor') . ')',
            'risk_moyen' => get_string('risk_moyen', 'local_student_monitor') . ' (' . $stats->moyen . ' ' .
                get_string('students', 'local_student_monitor') . ')',
            'risk_faible' => get_string('risk_faible', 'local_student_monitor') . ' (' . $stats->faible . ' ' .
                get_string('students', 'local_student_monitor') . ')',
        ];

        $mform->addElement('select', 'inactivity_level', get_string('selectinactivitylevel', 'local_student_monitor'),
            $inactivityoptions);
        $mform->addHelpButton('inactivity_level', 'inactivity_level', 'local_student_monitor');
        $mform->hideIf('inactivity_level', 'recipients', 'neq', 'by_inactivity_level');

        // Display student preview for selected inactivity level.
        $mform->addElement('html', '<div id="inactivity-level-preview" style="display:none; margin-top:15px;">
            <div class="alert alert-info">
                <strong>' . get_string('studentpreview', 'local_student_monitor') . ':</strong>
                <div id="preview-content"></div>
            </div>
        </div>');

        // Category selection (for category recipients).
        $categories = $this->get_categories();
        $mform->addElement('select', 'categoryid', get_string('category'), $categories);
        $mform->hideIf('categoryid', 'recipients', 'neq', 'category');

        // Course selection (for all_course and group recipients).
        $courses = $this->get_courses();
        $mform->addElement('select', 'courseid', get_string('course'), $courses);
        $mform->hideIf('courseid', 'recipients', 'eq', 'all_students');
        $mform->hideIf('courseid', 'recipients', 'eq', 'by_inactivity_level');
        $mform->hideIf('courseid', 'recipients', 'eq', 'manual');
        $mform->hideIf('courseid', 'recipients', 'eq', 'category');
        $mform->hideIf('courseid', 'recipients', 'eq', 'csv');

        // Group selection.
        $mform->addElement('select', 'groupid', get_string('group'), []);
        $mform->hideIf('groupid', 'recipients', 'neq', 'group');

        // Manual user selection with autocomplete.
        $options = [
            'multiple' => true,
            'ajax' => 'local_student_monitor/search_users',
            'valuehtmlcallback' => function($value) {
                global $DB;
                if (!$value) {
                    return '';
                }
                $user = $DB->get_record('user', ['id' => $value], 'id, firstname, lastname, email');
                if ($user) {
                    return fullname($user) . ' (' . $user->email . ')';
                }
                return '';
            }
        ];
        $mform->addElement('autocomplete', 'selectedusers', get_string('selectusersfield', 'local_student_monitor'), [], $options);
        $mform->hideIf('selectedusers', 'recipients', 'neq', 'manual');

        // CSV file upload.
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'local_student_monitor'), null, [
            'accepted_types' => ['.csv'],
        ]);
        $mform->addHelpButton('csvfile', 'csvfile', 'local_student_monitor');
        $mform->hideIf('csvfile', 'recipients', 'neq', 'csv');

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
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('previewalert', 'local_student_monitor'));
        $buttonarray[] = $mform->createElement('submit', 'submitandsend', get_string('sendalert', 'local_student_monitor'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
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
        if ($data['recipients'] == 'by_inactivity_level' && empty($data['inactivity_level'])) {
            $errors['inactivity_level'] = get_string('required');
        }

        if ($data['recipients'] == 'category' && empty($data['categoryid'])) {
            $errors['categoryid'] = get_string('required');
        }

        if ($data['recipients'] == 'all_course' && empty($data['courseid'])) {
            $errors['courseid'] = get_string('required');
        }

        if ($data['recipients'] == 'group' && empty($data['groupid'])) {
            $errors['groupid'] = get_string('required');
        }

        if ($data['recipients'] == 'manual' && empty($data['selectedusers'])) {
            $errors['selectedusers'] = get_string('selectusers', 'local_student_monitor');
        }

        if ($data['recipients'] == 'csv' && empty($data['csvfile'])) {
            $errors['csvfile'] = get_string('csvfilerequired', 'local_student_monitor');
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
     * Get available courses with category hierarchy.
     *
     * @return array Array of courses with category path
     */
    protected function get_courses() {
        global $DB;

        $courses = [0 => get_string('choosedots')];

        // Get all courses with their category information.
        $allcourses = $DB->get_records_sql(
            "SELECT c.id, c.fullname, c.category, cc.name as categoryname, cc.path
               FROM {course} c
               LEFT JOIN {course_categories} cc ON cc.id = c.category
              WHERE c.id != :siteid
              ORDER BY cc.path, c.fullname",
            ['siteid' => SITEID]
        );

        // Build category path cache.
        $categorypaths = $this->build_category_paths();

        foreach ($allcourses as $course) {
            $displayname = $course->fullname;

            // Add category hierarchy if available.
            if ($course->category && isset($categorypaths[$course->category])) {
                $displayname = $categorypaths[$course->category] . ' > ' . $course->fullname;
            }

            $courses[$course->id] = $displayname;
        }

        return $courses;
    }

    /**
     * Get available course categories with hierarchy.
     *
     * @return array Array of categories with full path
     */
    protected function get_categories() {
        $categories = [0 => get_string('choosedots')];

        // Build category paths.
        $categorypaths = $this->build_category_paths();

        // Sort by path to maintain hierarchy order.
        asort($categorypaths);

        foreach ($categorypaths as $id => $path) {
            $categories[$id] = $path;
        }

        return $categories;
    }

    /**
     * Build category paths for all visible categories.
     *
     * @return array Array of category ID => full path
     */
    protected function build_category_paths() {
        global $DB;

        $categorypaths = [];

        // Get all visible categories.
        $allcategories = $DB->get_records('course_categories', ['visible' => 1], 'path', 'id, name, path, parent, depth');

        // Build a map of category names.
        $categorynames = [];
        foreach ($allcategories as $category) {
            $categorynames[$category->id] = $category->name;
        }

        // Build full paths for each category.
        foreach ($allcategories as $category) {
            $pathparts = [];

            if ($category->path) {
                // Path format: /1/2/3 where numbers are category IDs.
                $pathids = explode('/', trim($category->path, '/'));

                foreach ($pathids as $pathid) {
                    if ($pathid && isset($categorynames[$pathid])) {
                        $pathparts[] = $categorynames[$pathid];
                    }
                }
            }

            if (empty($pathparts)) {
                // Fallback to just the category name.
                $pathparts[] = $category->name;
            }

            // Join with > to show hierarchy.
            $categorypaths[$category->id] = implode(' > ', $pathparts);
        }

        return $categorypaths;
    }
}
