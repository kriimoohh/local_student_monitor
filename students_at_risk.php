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
 * Students at Risk - Detailed view and filtering page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:viewdashboard', $context);

// Get filter parameters.
$risklevel = optional_param('risk', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);
$sort = optional_param('sort', 'risk', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);

// Validate risk level to prevent SQL injection and ensure only valid values.
if ($risklevel && !in_array($risklevel, ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'])) {
    $risklevel = '';
}

$PAGE->set_url(new moodle_url('/local/student_monitor/students_at_risk.php', [
    'risk' => $risklevel,
    'page' => $page,
    'perpage' => $perpage,
    'sort' => $sort,
    'dir' => $dir,
]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('studentsatrisk', 'local_student_monitor'));
$PAGE->set_heading(get_string('studentsatrisk', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Add CSS.
$PAGE->requires->css('/local/student_monitor/styles/styles.css');

// Initialize tracker.
$tracker = new \local_student_monitor\manager\student_tracker();

// Get statistics.
$stats = $tracker->get_statistics();

// Build SQL query with filters.
$sql = "SELECT st.*, u.firstname, u.lastname, u.email, u.picture, u.imagealt, u.firstnamephonetic, u.lastnamephonetic,
               u.middlename, u.alternatename
          FROM {local_sm_student_tracking} st
          JOIN {user} u ON u.id = st.userid
         WHERE u.deleted = 0 AND u.suspended = 0";

$params = [];
$countsql = "SELECT COUNT(DISTINCT st.id)
               FROM {local_sm_student_tracking} st
               JOIN {user} u ON u.id = st.userid
              WHERE u.deleted = 0 AND u.suspended = 0";

if ($risklevel) {
    $sql .= " AND st.risk_level = :risklevel";
    $countsql .= " AND st.risk_level = :risklevel";
    $params['risklevel'] = $risklevel;
} else {
    // Only show students with at least MEDIUM risk.
    $sql .= " AND st.risk_level IN ('MEDIUM', 'HIGH', 'CRITICAL')";
    $countsql .= " AND st.risk_level IN ('MEDIUM', 'HIGH', 'CRITICAL')";
}

// Add sorting.
switch ($sort) {
    case 'name':
        $sql .= " ORDER BY u.lastname $dir, u.firstname $dir";
        break;
    case 'email':
        $sql .= " ORDER BY u.email $dir";
        break;
    case 'inactivity':
        $sql .= " ORDER BY st.inactivity_days $dir";
        break;
    case 'assignments':
        $sql .= " ORDER BY st.missing_activities $dir";
        break;
    case 'notifications':
        $sql .= " ORDER BY st.notification_count $dir";
        break;
    case 'risk':
    default:
        $sql .= " ORDER BY
                    CASE st.risk_level
                        WHEN 'CRITICAL' THEN 1
                        WHEN 'HIGH' THEN 2
                        WHEN 'MEDIUM' THEN 3
                        ELSE 4
                    END $dir,
                    st.inactivity_days DESC";
        break;
}

// Get total count.
$totalcount = $DB->count_records_sql($countsql, $params);

// Get students for current page.
$students = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

// Output starts here.
echo $OUTPUT->header();

// Page title with icon.
echo html_writer::tag('h2', '⚠️ ' . get_string('studentsatrisk', 'local_student_monitor'));

// Statistics Overview Cards.
echo html_writer::start_div('row mb-4');

// CRITIQUE.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card border-danger');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('risk_critique', 'local_student_monitor'), ['class' => 'card-title text-danger']);
echo html_writer::tag('div', $stats->critique, ['class' => 'display-4 text-danger']);
$url = new moodle_url($PAGE->url, ['risk' => 'CRITICAL']);
echo html_writer::link($url, get_string('viewstudents', 'local_student_monitor'), ['class' => 'btn btn-sm btn-danger mt-2']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// ÉLEVÉ.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card border-warning');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('risk_eleve', 'local_student_monitor'), ['class' => 'card-title text-warning']);
echo html_writer::tag('div', $stats->eleve, ['class' => 'display-4 text-warning']);
$url = new moodle_url($PAGE->url, ['risk' => 'HIGH']);
echo html_writer::link($url, get_string('viewstudents', 'local_student_monitor'), ['class' => 'btn btn-sm btn-warning mt-2']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// MOYEN.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card border-info');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('risk_moyen', 'local_student_monitor'), ['class' => 'card-title text-info']);
echo html_writer::tag('div', $stats->moyen, ['class' => 'display-4 text-info']);
$url = new moodle_url($PAGE->url, ['risk' => 'MEDIUM']);
echo html_writer::link($url, get_string('viewstudents', 'local_student_monitor'), ['class' => 'btn btn-sm btn-info mt-2']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// FAIBLE.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card border-success');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('risk_faible', 'local_student_monitor'), ['class' => 'card-title text-success']);
echo html_writer::tag('div', $stats->faible, ['class' => 'display-4 text-success']);
$url = new moodle_url($PAGE->url, ['risk' => 'LOW']);
echo html_writer::link($url, get_string('viewstudents', 'local_student_monitor'), ['class' => 'btn btn-sm btn-success mt-2']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // row.

// Current filter display.
echo html_writer::start_div('alert alert-info mb-3');
if ($risklevel) {
    echo html_writer::tag('strong', get_string('currentfilter', 'local_student_monitor') . ': ');
    // Normalize risk level for translation (remove accents).
    $riskkey = mb_strtolower($risklevel, 'UTF-8');
    $riskkey = str_replace(['é', 'è', 'ê', 'ë'], 'e', $riskkey);
    echo get_string('risk_' . $riskkey, 'local_student_monitor');
    echo ' (' . $totalcount . ' ' . get_string('student', 'local_student_monitor') . ') ';
    $clearurl = new moodle_url($PAGE->url, ['risk' => '']);
    echo html_writer::link($clearurl, get_string('clearfilter', 'local_student_monitor'),
        ['class' => 'btn btn-sm btn-secondary ml-2']);
} else {
    echo get_string('showingatrisk', 'local_student_monitor') . ' (' . $totalcount . ' ' .
        get_string('student', 'local_student_monitor') . ')';
}
echo html_writer::end_div();

// Quick actions.
echo html_writer::start_div('mb-3');
$exporturl = new moodle_url('/local/student_monitor/export.php', ['format' => 'csv', 'risk' => $risklevel]);
echo html_writer::link($exporturl, '📥 ' . get_string('exportcsv', 'local_student_monitor'),
    ['class' => 'btn btn-success mr-2']);

$createalerturl = new moodle_url('/local/student_monitor/create_alert.php');
echo html_writer::link($createalerturl, '📧 ' . get_string('createalert', 'local_student_monitor'),
    ['class' => 'btn btn-primary mr-2']);

$dashboardurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($dashboardurl, '← ' . get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary']);
echo html_writer::end_div();

// Students table.
if (!empty($students)) {
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body');

    // Per page selector.
    echo html_writer::start_div('mb-3 text-right');
    echo html_writer::tag('label', get_string('perpage', 'local_student_monitor') . ': ', ['class' => 'mr-2']);
    foreach ([25, 50, 100, 200] as $pp) {
        $ppurl = new moodle_url($PAGE->url, ['perpage' => $pp, 'page' => 0]);
        $class = $perpage == $pp ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
        echo html_writer::link($ppurl, $pp, ['class' => $class . ' mr-1']);
    }
    echo html_writer::end_div();

    $table = new html_table();
    $table->attributes['class'] = 'table table-striped table-hover';

    // Table headers with sorting.
    $sorticon = $dir === 'ASC' ? '▲' : '▼';

    $nameurl = new moodle_url($PAGE->url, ['sort' => 'name', 'dir' => ($sort === 'name' && $dir === 'ASC') ? 'DESC' : 'ASC']);
    $nameheader = html_writer::link($nameurl, get_string('studentname', 'local_student_monitor'));
    if ($sort === 'name') {
        $nameheader .= ' ' . $sorticon;
    }

    $emailurl = new moodle_url($PAGE->url, ['sort' => 'email', 'dir' => ($sort === 'email' && $dir === 'ASC') ? 'DESC' : 'ASC']);
    $emailheader = html_writer::link($emailurl, get_string('email'));
    if ($sort === 'email') {
        $emailheader .= ' ' . $sorticon;
    }

    $riskurl = new moodle_url($PAGE->url, ['sort' => 'risk', 'dir' => ($sort === 'risk' && $dir === 'ASC') ? 'DESC' : 'ASC']);
    $riskheader = html_writer::link($riskurl, get_string('risklevel', 'local_student_monitor'));
    if ($sort === 'risk') {
        $riskheader .= ' ' . $sorticon;
    }

    $inactivityurl = new moodle_url($PAGE->url, [
        'sort' => 'inactivity',
        'dir' => ($sort === 'inactivity' && $dir === 'ASC') ? 'DESC' : 'ASC',
    ]);
    $inactivityheader = html_writer::link($inactivityurl, get_string('inactivitydays', 'local_student_monitor'));
    if ($sort === 'inactivity') {
        $inactivityheader .= ' ' . $sorticon;
    }

    $assignmentsurl = new moodle_url($PAGE->url, [
        'sort' => 'assignments',
        'dir' => ($sort === 'assignments' && $dir === 'ASC') ? 'DESC' : 'ASC',
    ]);
    $assignmentsheader = html_writer::link($assignmentsurl, get_string('missingassignments', 'local_student_monitor'));
    if ($sort === 'assignments') {
        $assignmentsheader .= ' ' . $sorticon;
    }

    $notificationsurl = new moodle_url($PAGE->url, [
        'sort' => 'notifications',
        'dir' => ($sort === 'notifications' && $dir === 'ASC') ? 'DESC' : 'ASC',
    ]);
    $notificationsheader = html_writer::link($notificationsurl, get_string('notificationcount', 'local_student_monitor'));
    if ($sort === 'notifications') {
        $notificationsheader .= ' ' . $sorticon;
    }

    $table->head = [
        '',  // Profile picture.
        $nameheader,
        $emailheader,
        $riskheader,
        get_string('lastactivity', 'local_student_monitor'),
        $inactivityheader,
        $assignmentsheader,
        $notificationsheader,
        get_string('actions', 'local_student_monitor'),
    ];

    foreach ($students as $student) {
        // Profile picture.
        $userpic = $OUTPUT->user_picture($student, ['size' => 35, 'class' => 'rounded-circle']);

        // Risk badge.
        $riskclass = 'badge ';
        switch ($student->risk_level) {
            case 'CRITICAL':
                $riskclass .= 'badge-danger';
                break;
            case 'HIGH':
                $riskclass .= 'badge-warning';
                break;
            case 'MEDIUM':
                $riskclass .= 'badge-info';
                break;
            default:
                $riskclass .= 'badge-success';
        }
        $riskbadge = html_writer::tag('span', $student->risk_level, ['class' => $riskclass]);

        // Last activity.
        $lastactivity = $student->last_activity ? userdate($student->last_activity, get_string('strftimedatetime')) : '-';

        // Inactivity days with icon.
        $inactivitytext = $student->inactivity_days;
        if ($student->inactivity_days > 14) {
            $inactivitytext = html_writer::tag('span', '🔴 ' . $student->inactivity_days,
                ['class' => 'text-danger font-weight-bold']);
        } else if ($student->inactivity_days > 7) {
            $inactivitytext = html_writer::tag('span', '🟠 ' . $student->inactivity_days,
                ['class' => 'text-warning font-weight-bold']);
        }

        // Missing activities with icon.
        $assignmentstext = $student->missing_activities;
        if ($student->missing_activities >= 5) {
            $assignmentstext = html_writer::tag('span', '❗ ' . $student->missing_activities,
                ['class' => 'text-danger font-weight-bold']);
        } else if ($student->missing_activities >= 3) {
            $assignmentstext = html_writer::tag('span', '⚠️ ' . $student->missing_activities,
                ['class' => 'text-warning']);
        }

        // Actions.
        $actions = '';
        $viewurl = new moodle_url('/user/profile.php', ['id' => $student->userid]);
        $actions .= html_writer::link($viewurl, get_string('viewprofile', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-primary mr-1', 'target' => '_blank']);

        // Send notification button.
        $notifyurl = new moodle_url('/local/student_monitor/create_alert.php', ['userid' => $student->userid]);
        $actions .= html_writer::link($notifyurl, '📧', ['class' => 'btn btn-sm btn-success', 'title' => get_string('sendnotification', 'local_student_monitor')]);

        $row = [
            $userpic,
            fullname($student),
            $student->email,
            $riskbadge,
            $lastactivity,
            $inactivitytext,
            $assignmentstext,
            $student->notification_count,
            $actions,
        ];

        $table->data[] = $row;
    }

    echo html_writer::table($table);

    // Pagination.
    $baseurl = new moodle_url($PAGE->url);
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);

    echo html_writer::end_div(); // card-body.
    echo html_writer::end_div(); // card.
} else {
    echo html_writer::tag('div', get_string('nostudentsatrisk', 'local_student_monitor'),
        ['class' => 'alert alert-success']);
}

echo $OUTPUT->footer();
