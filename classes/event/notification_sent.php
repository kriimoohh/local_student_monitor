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
 * The notification_sent event.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event triggered when a notification is sent.
 */
class notification_sent extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_sm_notifications';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_notification_sent', 'local_student_monitor');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $type = $this->other['type'] ?? 'unknown';
        $success = $this->other['success'] ?? false;
        $status = $success ? 'successfully' : 'unsuccessfully';

        return "Notification of type '{$type}' was {$status} sent to user {$this->userid}";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/student_monitor/view_alerts.php');
    }
}
