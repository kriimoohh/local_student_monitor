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
 * Advanced reports page with charts and analytics.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:viewreports', context_system::instance());

$PAGE->set_url(new moodle_url('/local/student_monitor/reports.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('advancedreports', 'local_student_monitor'));
$PAGE->set_heading(get_string('advancedreports', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Get managers.
$studenttracker = new \local_student_monitor\manager\student_tracker();
$reportingmanager = new \local_student_monitor\manager\reporting_manager();

// Get statistics.
$stats = $studenttracker->get_statistics();

// Get risk distribution for chart.
$riskdistribution = [
    'faible' => $stats->faible,
    'moyen' => $stats->moyen,
    'eleve' => $stats->eleve,
    'critique' => $stats->critique,
    'labels' => [
        'faible' => get_string('risk_faible', 'local_student_monitor'),
        'moyen' => get_string('risk_moyen', 'local_student_monitor'),
        'eleve' => get_string('risk_eleve', 'local_student_monitor'),
        'critique' => get_string('risk_critique', 'local_student_monitor'),
    ],
    'title' => get_string('riskdistribution', 'local_student_monitor')
];

// Get notification trends (last 30 days).
$trends = $reportingmanager->get_notification_trends(30);
$trendlabels = [];
$trendssent = [];
$trendsread = [];

foreach ($trends as $trend) {
    $trendlabels[] = userdate($trend->date, get_string('strftimedayshort', 'core_langconfig'));
    $trendssent[] = $trend->sent;
    $trendsread[] = $trend->read;
}

$notificationtrends = [
    'labels' => $trendlabels,
    'sent' => $trendssent,
    'read' => $trendsread,
    'sentLabel' => get_string('notificationssent', 'local_student_monitor'),
    'readLabel' => get_string('notificationsread', 'local_student_monitor'),
    'title' => get_string('notificationtrends', 'local_student_monitor')
];

// Get activity by notification type.
$activitydata = $DB->get_records_sql("
    SELECT notification_type, COUNT(*) as count
    FROM {local_sm_notifications}
    WHERE timecreated >= :timestart
    GROUP BY notification_type
    ORDER BY count DESC
", ['timestart' => time() - (30 * 24 * 60 * 60)]);

$activitylabels = [];
$activityvalues = [];

foreach ($activitydata as $activity) {
    $activitylabels[] = get_string($activity->notification_type, 'local_student_monitor');
    $activityvalues[] = $activity->count;
}

$activitytrends = [
    'labels' => $activitylabels,
    'values' => $activityvalues,
    'datasetLabel' => get_string('notifications', 'local_student_monitor'),
    'title' => get_string('notificationtypes', 'local_student_monitor')
];

// Get intervention stats by risk level.
$interventions = $DB->get_records_sql("
    SELECT risk_level, COUNT(*) as count
    FROM {local_sm_student_tracking}
    WHERE assigned_to IS NOT NULL
    GROUP BY risk_level
    ORDER BY
        CASE risk_level
            WHEN 'CRITIQUE' THEN 1
            WHEN 'ÉLEVÉ' THEN 2
            WHEN 'MOYEN' THEN 3
            WHEN 'FAIBLE' THEN 4
        END
");

$interventionlabels = [];
$interventionvalues = [];

foreach ($interventions as $intervention) {
    $interventionlabels[] = $intervention->risk_level;
    $interventionvalues[] = $intervention->count;
}

$interventionstats = [
    'labels' => $interventionlabels,
    'values' => $interventionvalues,
    'datasetLabel' => get_string('interventions', 'local_student_monitor'),
    'title' => get_string('interventionsbyrisk', 'local_student_monitor')
];

// Prepare charts data for JavaScript.
$chartsdata = [
    'riskDistribution' => $riskdistribution,
    'notificationTrends' => $notificationtrends,
    'activityTrends' => $activitytrends,
    'interventionStats' => $interventionstats
];

// Initialize Chart.js module.
$PAGE->requires->js_call_amd('local_student_monitor/charts', 'init', [$chartsdata]);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('advancedreports', 'local_student_monitor'));

// Summary statistics.
echo html_writer::start_div('row mb-4');

// Total students card.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalstudents', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_students, ['class' => 'kpi-number']);
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

// Total notifications card.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalnotifications', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', array_sum($trendssent), ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('last30days', 'local_student_monitor'));
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

// At risk students card.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-dark');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('studentsatrisk', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', ($stats->critique + $stats->eleve), ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('criticalandhigh', 'local_student_monitor'));
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

// Read rate card.
$totalread = array_sum($trendsread);
$totalsent = array_sum($trendssent);
$readrate = $totalsent > 0 ? round(($totalread / $totalsent) * 100, 1) : 0;

echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('readrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $readrate . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('last30days', 'local_student_monitor'));
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

echo html_writer::end_div(); // Row.

// Charts section.
echo html_writer::start_div('row mt-4');

// Risk distribution chart.
echo html_writer::start_div('col-md-6 mb-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'riskDistributionChart']);
echo html_writer::end_div(); // Chart-container.
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

// Notification trends chart.
echo html_writer::start_div('col-md-6 mb-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'notificationTrendsChart']);
echo html_writer::end_div(); // Chart-container.
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

echo html_writer::end_div(); // Row.

echo html_writer::start_div('row');

// Activity trends chart.
echo html_writer::start_div('col-md-6 mb-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'activityTrendsChart']);
echo html_writer::end_div(); // Chart-container.
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

// Intervention stats chart.
echo html_writer::start_div('col-md-6 mb-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'interventionStatsChart']);
echo html_writer::end_div(); // Chart-container.
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.
echo html_writer::end_div(); // Col.

echo html_writer::end_div(); // Row.

// Export buttons.
echo html_writer::start_div('row mt-4');
echo html_writer::start_div('col-12');
echo html_writer::tag('h4', get_string('exportdata', 'local_student_monitor'));
echo html_writer::start_div('btn-group', ['role' => 'group']);

$exportstudentsurl = new moodle_url('/local/student_monitor/export.php', ['type' => 'students']);
echo html_writer::link($exportstudentsurl, get_string('exportstudents', 'local_student_monitor'),
    ['class' => 'btn btn-primary']);

$exportnotificationsurl = new moodle_url('/local/student_monitor/export.php', ['type' => 'notifications']);
echo html_writer::link($exportnotificationsurl, get_string('exportnotifications', 'local_student_monitor'),
    ['class' => 'btn btn-info']);

echo html_writer::end_div(); // Btn-group.
echo html_writer::end_div(); // Col.
echo html_writer::end_div(); // Row.

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
