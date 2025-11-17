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
 * Course settings form.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for course-specific Student Monitor settings.
 */
class course_settings_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $course = $this->_customdata['course'];

        // Course ID (hidden).
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        // General section.
        $mform->addElement('header', 'generalhdr', get_string('generalsettings', 'local_student_monitor'));

        // Enable monitoring for this course.
        $mform->addElement('advcheckbox', 'enabled', get_string('enablemonitoring', 'local_student_monitor'));
        $mform->addHelpButton('enabled', 'enablemonitoring', 'local_student_monitor');
        $mform->setDefault('enabled', 1);

        // New content notifications.
        $mform->addElement('header', 'newcontenthdr', get_string('newcontentnotifications', 'local_student_monitor'));

        $mform->addElement('advcheckbox', 'notify_new_content',
            get_string('notifynewcontent', 'local_student_monitor'),
            get_string('notifynewcontent_desc', 'local_student_monitor'));
        $mform->setDefault('notify_new_content', 1);

        // Activity types to monitor.
        $mform->addElement('static', 'activitytypes_label',
            get_string('activitytypes', 'local_student_monitor'),
            get_string('activitytypes_desc', 'local_student_monitor'));

        $activitytypes = [
            'assign' => get_string('assignment', 'assign'),
            'quiz' => get_string('quiz', 'quiz'),
            'forum' => get_string('forum', 'forum'),
            'resource' => get_string('resource', 'resource'),
            'url' => get_string('url', 'url'),
            'page' => get_string('page', 'page'),
        ];

        foreach ($activitytypes as $type => $label) {
            $mform->addElement('advcheckbox', 'activity_' . $type, $label);
            $mform->setDefault('activity_' . $type, 1);
            $mform->hideIf('activity_' . $type, 'notify_new_content', 'notchecked');
        }

        // Assignment reminders section.
        $mform->addElement('header', 'assignmentremindershdr',
            get_string('assignmentremindersettings', 'local_student_monitor'));

        $mform->addElement('advcheckbox', 'assignment_reminders',
            get_string('enableassignmentreminders', 'local_student_monitor'));
        $mform->setDefault('assignment_reminders', 1);

        // Custom reminder days for this course.
        $mform->addElement('text', 'reminder_days_custom',
            get_string('customreminderdays', 'local_student_monitor'));
        $mform->setType('reminder_days_custom', PARAM_TEXT);
        $mform->addHelpButton('reminder_days_custom', 'customreminderdays', 'local_student_monitor');
        $mform->setDefault('reminder_days_custom', '7,3,1');
        $mform->hideIf('reminder_days_custom', 'assignment_reminders', 'notchecked');

        // Inactivity monitoring section.
        $mform->addElement('header', 'inactivityhdr',
            get_string('inactivitysettings', 'local_student_monitor'));

        $mform->addElement('advcheckbox', 'monitor_inactivity',
            get_string('monitorinactivity', 'local_student_monitor'));
        $mform->setDefault('monitor_inactivity', 1);

        // Custom thresholds for this course.
        $mform->addElement('text', 'inactivity_threshold_custom',
            get_string('custominactivitythreshold', 'local_student_monitor'),
            ['size' => 10]);
        $mform->setType('inactivity_threshold_custom', PARAM_INT);
        $mform->addHelpButton('inactivity_threshold_custom', 'custominactivitythreshold', 'local_student_monitor');
        $mform->hideIf('inactivity_threshold_custom', 'monitor_inactivity', 'notchecked');

        // Supervisors section.
        $mform->addElement('header', 'supervisorshdr', get_string('supervisors', 'local_student_monitor'));

        // Assign default supervisor for this course.
        $supervisors = $this->get_course_supervisors($course->id);
        $mform->addElement('select', 'default_supervisor',
            get_string('defaultsupervisor', 'local_student_monitor'),
            $supervisors);
        $mform->addHelpButton('default_supervisor', 'defaultsupervisor', 'local_student_monitor');

        // Notification preferences.
        $mform->addElement('header', 'notificationshdr', get_string('notificationpreferences', 'local_student_monitor'));

        // Send digest to teachers.
        $mform->addElement('advcheckbox', 'teacher_digest',
            get_string('teacherdigest', 'local_student_monitor'),
            get_string('teacherdigest_desc', 'local_student_monitor'));
        $mform->setDefault('teacher_digest', 0);

        // Digest frequency.
        $mform->addElement('select', 'digest_frequency',
            get_string('digestfrequency', 'local_student_monitor'),
            [
                'daily' => get_string('daily', 'local_student_monitor'),
                'weekly' => get_string('weekly', 'local_student_monitor'),
            ]);
        $mform->setDefault('digest_frequency', 'weekly');
        $mform->hideIf('digest_frequency', 'teacher_digest', 'notchecked');

        // Action buttons.
        $this->add_action_buttons();
    }

    /**
     * Get course supervisors/teachers.
     *
     * @param int $courseid Course ID
     * @return array Array of supervisors
     */
    protected function get_course_supervisors($courseid) {
        $context = \context_course::instance($courseid);

        $supervisors = [0 => get_string('none')];

        // Get teachers and managers.
        $teachers = get_enrolled_users($context, 'local/student_monitor:intervene', 0, 'u.id, u.firstname, u.lastname');

        foreach ($teachers as $teacher) {
            $supervisors[$teacher->id] = fullname($teacher);
        }

        return $supervisors;
    }
}
