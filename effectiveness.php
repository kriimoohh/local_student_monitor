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
 * Intervention effectiveness reports.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:viewreports', context_system::instance());

$period = optional_param('period', 'month', PARAM_ALPHA);
$supervisorid = optional_param('supervisor', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/effectiveness.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('effectivenessreports', 'local_student_monitor'));
$PAGE->set_heading(get_string('effectivenessreports', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Calculate date range.
switch ($period) {
    case 'week':
        $startdate = strtotime('monday this week');
        $enddate = time();
        break;
    case 'month':
        $startdate = strtotime('first day of this month');
        $enddate = time();
        break;
    case 'quarter':
        $startdate = strtotime('first day of this month -3 months');
        $enddate = time();
        break;
    case 'year':
        $startdate = strtotime('first day of january this year');
        $enddate = time();
        break;
    default:
        $startdate = strtotime('first day of this month');
        $enddate = time();
}

// Get intervention tracker.
$interventiontracker = new \local_student_monitor\manager\intervention_tracker();

// Get effectiveness metrics.
$metrics = $interventiontracker->get_effectiveness_metrics($startdate, $enddate);

// Get supervisor statistics.
if ($supervisorid > 0) {
    $supervisorstats = $interventiontracker->get_supervisor_statistics($supervisorid, $startdate, $enddate);
} else {
    $supervisorstats = null;
}

// Get all supervisors for filter.
$supervisors = get_users_by_capability(
    context_system::instance(),
    'local/student_monitor:intervene',
    'u.id, u.firstname, u.lastname',
    'u.lastname, u.firstname'
);

// Get risk transition data.
$risktransitions = $DB->get_records_sql("
    SELECT
        l1.userid,
        MAX(CASE WHEN l1.timecreated = (
            SELECT MIN(l2.timecreated)
            FROM {local_sm_logs} l2
            WHERE l2.userid = l1.userid
              AND l2.action = 'risk_level_changed'
              AND l2.timecreated >= :startdate1
        ) THEN JSON_EXTRACT(l1.details, '$.old_level') END) as old_level,
        MAX(CASE WHEN l1.timecreated = (
            SELECT MAX(l2.timecreated)
            FROM {local_sm_logs} l2
            WHERE l2.userid = l1.userid
              AND l2.action = 'risk_level_changed'
              AND l2.timecreated <= :enddate1
        ) THEN JSON_EXTRACT(l1.details, '$.new_level') END) as new_level
    FROM {local_sm_logs} l1
    WHERE l1.action = 'risk_level_changed'
      AND l1.timecreated >= :startdate2
      AND l1.timecreated <= :enddate2
    GROUP BY l1.userid
", [
    'startdate1' => $startdate,
    'enddate1' => $enddate,
    'startdate2' => $startdate,
    'enddate2' => $enddate
]);

// Calculate improvement/deterioration counts.
$improved = 0;
$deteriorated = 0;
$stable = 0;

$riskhierarchy = ['FAIBLE' => 1, 'MOYEN' => 2, 'ÉLEVÉ' => 3, 'CRITIQUE' => 4];

foreach ($risktransitions as $transition) {
    if (!$transition->old_level || !$transition->new_level) {
        continue;
    }

    $oldrank = $riskhierarchy[$transition->old_level] ?? 0;
    $newrank = $riskhierarchy[$transition->new_level] ?? 0;

    if ($newrank < $oldrank) {
        $improved++;
    } else if ($newrank > $oldrank) {
        $deteriorated++;
    } else {
        $stable++;
    }
}

// Get intervention type distribution.
$interventiontypes = $DB->get_records_sql("
    SELECT
        action,
        COUNT(*) as count
    FROM {local_sm_logs}
    WHERE action LIKE '%intervention%'
      AND timecreated >= :startdate
      AND timecreated <= :enddate
    GROUP BY action
    ORDER BY count DESC
", ['startdate' => $startdate, 'enddate' => $enddate]);

// Prepare chart data for risk transitions.
$transitionchartdata = [
    'labels' => [
        get_string('improved', 'local_student_monitor'),
        get_string('stable', 'local_student_monitor'),
        get_string('deteriorated', 'local_student_monitor')
    ],
    'data' => [$improved, $stable, $deteriorated],
    'colors' => ['#28a745', '#ffc107', '#dc3545']
];

// Initialize JavaScript.
$PAGE->requires->js_call_amd('local_student_monitor/effectiveness_charts', 'init', [$transitionchartdata]);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('effectivenessreports', 'local_student_monitor'));

// Period and supervisor filters.
echo html_writer::start_div('row mb-3');

// Period filter.
echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('period', 'local_student_monitor') . ': ', ['for' => 'period-select']);
echo html_writer::start_tag('select', [
    'id' => 'period-select',
    'class' => 'custom-select',
    'onchange' => 'window.location.href="?period=" + this.value + "&supervisor=' . $supervisorid . '"'
]);
echo html_writer::tag('option', get_string('thisweek', 'local_student_monitor'),
    ['value' => 'week', 'selected' => ($period === 'week')]);
echo html_writer::tag('option', get_string('thismonth', 'local_student_monitor'),
    ['value' => 'month', 'selected' => ($period === 'month')]);
echo html_writer::tag('option', get_string('thisquarter', 'local_student_monitor'),
    ['value' => 'quarter', 'selected' => ($period === 'quarter')]);
echo html_writer::tag('option', get_string('thisyear', 'local_student_monitor'),
    ['value' => 'year', 'selected' => ($period === 'year')]);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Supervisor filter.
echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('supervisor', 'local_student_monitor') . ': ', ['for' => 'supervisor-select']);
echo html_writer::start_tag('select', [
    'id' => 'supervisor-select',
    'class' => 'custom-select',
    'onchange' => 'window.location.href="?period=' . $period . '&supervisor=" + this.value'
]);
echo html_writer::tag('option', get_string('allsupervisors', 'local_student_monitor'),
    ['value' => '0', 'selected' => ($supervisorid === 0)]);
foreach ($supervisors as $supervisor) {
    echo html_writer::tag('option', fullname($supervisor),
        ['value' => $supervisor->id, 'selected' => ($supervisorid == $supervisor->id)]);
}
echo html_writer::end_tag('select');
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Overall effectiveness KPIs.
echo html_writer::tag('h3', get_string('overalleffectiveness', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Students improved.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('studentsimproved', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $metrics->students_improved, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Students at risk.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('studentsatrisk', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $metrics->students_at_risk, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Success rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('successrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $metrics->success_rate . '%', ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Average interventions.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('avginterventions', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $metrics->avg_interventions_per_student, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('perstudent', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Supervisor-specific statistics.
if ($supervisorstats) {
    echo html_writer::tag('h3', get_string('supervisorperformance', 'local_student_monitor'), ['class' => 'mt-4']);

    echo html_writer::start_div('row mb-4');

    // Tasks completed.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card kpi-card bg-success text-white');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('taskscompleted', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', $supervisorstats->tasks_completed, ['class' => 'kpi-number']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Tasks pending.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card kpi-card bg-warning text-white');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('taskspending', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', $supervisorstats->tasks_pending, ['class' => 'kpi-number']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Tasks overdue.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card kpi-card bg-danger text-white');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('tasksoverdue', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', $supervisorstats->tasks_overdue, ['class' => 'kpi-number']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Average response time.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card kpi-card bg-info text-white');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('avgresponsetime', 'local_student_monitor'), ['class' => 'card-title']);
    $hours = round($supervisorstats->avg_response_time / 3600, 1);
    echo html_writer::tag('div', $hours, ['class' => 'kpi-number']);
    echo html_writer::tag('small', get_string('hours', 'local_student_monitor'));
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    echo html_writer::end_div(); // Row.
}

// Risk transition chart.
echo html_writer::start_div('row mt-4');
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('risktransitions', 'local_student_monitor'));
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'transitionChart']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Intervention type distribution.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('interventiontypes', 'local_student_monitor'));

if (empty($interventiontypes)) {
    echo html_writer::tag('p', get_string('nodata', 'local_student_monitor'), ['class' => 'text-muted']);
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-sm']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('type', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('count', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($interventiontypes as $type) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string($type->action, 'local_student_monitor'));
        echo html_writer::tag('td', $type->count);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Export button.
echo html_writer::start_div('mt-3');
$exporturl = new moodle_url('/local/student_monitor/export_pdf.php', [
    'type' => 'effectiveness',
    'period' => $period,
    'supervisor' => $supervisorid
]);
echo html_writer::link($exporturl, get_string('exportpdf', 'local_student_monitor'),
    ['class' => 'btn btn-primary']);

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary ml-2']);
echo html_writer::end_div();

echo $OUTPUT->footer();
