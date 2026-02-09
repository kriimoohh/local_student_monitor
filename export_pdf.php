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
 * PDF export handler.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('local/student_monitor:viewreports', context_system::instance());

$type = required_param('type', PARAM_ALPHA);
$risklevel = optional_param('risk', '', PARAM_TEXT);
$startdate = optional_param('startdate', 0, PARAM_INT);
$enddate = optional_param('enddate', 0, PARAM_INT);

// Validate risk level to prevent SQL injection and ensure only valid values.
if ($risklevel && !in_array($risklevel, ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'])) {
    $risklevel = '';
}

$pdfmanager = new \local_student_monitor\manager\pdf_manager();

switch ($type) {
    case 'students':
        $pdfmanager->export_students_pdf($risklevel);
        break;

    case 'detailed':
        $pdfmanager->export_detailed_report_pdf();
        break;

    case 'notifications':
        $pdfmanager->export_notifications_pdf($startdate, $enddate);
        break;

    default:
        print_error('invalidexporttype', 'local_student_monitor');
}

exit;
