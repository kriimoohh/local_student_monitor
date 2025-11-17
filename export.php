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
 * Export data page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:exportdata', $context);

$format = required_param('format', PARAM_ALPHA);
$type = optional_param('type', 'students', PARAM_ALPHA);
$risklevel = optional_param('risk', '', PARAM_ALPHA);

// Get reporting manager.
$reportingmanager = new \local_student_monitor\manager\reporting_manager();

// Generate export based on type.
$filename = 'student_monitor_export_' . date('Y-m-d') . '.csv';
$content = '';

switch ($type) {
    case 'students':
        $content = $reportingmanager->export_students_csv($risklevel);
        $filename = 'student_monitor_students_' . date('Y-m-d') . '.csv';
        break;

    case 'notifications':
        $startdate = optional_param('startdate', time() - (30 * 86400), PARAM_INT);
        $enddate = optional_param('enddate', time(), PARAM_INT);
        $content = $reportingmanager->export_notifications_csv($startdate, $enddate);
        $filename = 'student_monitor_notifications_' . date('Y-m-d') . '.csv';
        break;

    default:
        print_error('invalidexporttype', 'local_student_monitor');
}

// Send file.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF"; // UTF-8 BOM
echo $content;
exit;
