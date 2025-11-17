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
 * Business Intelligence Dashboard for administrators.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managesettings', context_system::instance());

$days = optional_param('days', 30, PARAM_INT);
$cohortby = optional_param('cohort', 'course', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/student_monitor/bi_dashboard.php', [
    'days' => $days,
    'cohort' => $cohortby
]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('bidashboard', 'local_student_monitor'));
$PAGE->set_heading(get_string('bidashboard', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->js_call_amd('local_student_monitor/bi_charts', 'init', [$days]);

$biengine = new \local_student_monitor\manager\bi_analytics_engine();

// Get all analytics data.
$overview = $biengine->get_institutional_overview();
$trends = $biengine->get_trend_data($days);
$supervisors = $biengine->get_supervisor_performance();
$retention = $biengine->get_retention_analytics(90);
$cohorts = $biengine->get_cohort_analysis($cohortby);

echo $OUTPUT->header();

echo html_writer::tag('h2', '📊 ' . get_string('bidashboard', 'local_student_monitor'));

// Time period selector.
echo html_writer::start_div('mb-3');
$periodurl = new moodle_url($PAGE->url);

$periods = [7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '1 year'];
foreach ($periods as $d => $label) {
    $periodurl->param('days', $d);
    $class = $days === $d ? 'btn btn-primary' : 'btn btn-outline-primary';
    echo html_writer::link($periodurl, $label, ['class' => $class . ' mr-2']);
}
echo html_writer::end_div();

// Institutional Overview KPIs.
echo html_writer::tag('h3', get_string('institutionaloverview', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Total students.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-primary text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('totalstudents', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $overview->total_students, ['class' => 'display-3']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Needs intervention.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-danger text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('needsintervention', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $overview->needs_intervention, ['class' => 'display-3']);
$percentage = $overview->total_students > 0 ?
    round(($overview->needs_intervention / $overview->total_students) * 100, 1) : 0;
echo html_writer::tag('small', $percentage . '% of total');
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Success rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-success text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('successrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $overview->success_rate . '%', ['class' => 'display-3']);
echo html_writer::tag('small', get_string('studentsimproved', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Avg response time.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-info text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('avgresponsetime', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $overview->avg_response_time . 'h', ['class' => 'display-3']);
echo html_writer::tag('small', get_string('hoursaverage', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Risk Distribution.
echo html_writer::tag('h3', get_string('riskdistribution', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

foreach ($overview->risk_distribution as $risk) {
    $riskclass = [
        'FAIBLE' => 'success',
        'MOYEN' => 'warning',
        'ÉLEVÉ' => 'danger',
        'CRITIQUE' => 'dark'
    ][$risk->risk_level] ?? 'secondary';

    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card border-' . $riskclass);
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h6', $risk->risk_level, ['class' => 'card-title']);
    echo html_writer::tag('div', $risk->count, ['class' => 'display-4 text-' . $riskclass]);
    $pct = $overview->total_students > 0 ? round(($risk->count / $overview->total_students) * 100, 1) : 0;
    echo html_writer::tag('small', $pct . '%', ['class' => 'text-muted']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div(); // Row.

// Charts.
echo html_writer::tag('h3', get_string('trendsandcharts', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Daily interventions trend.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('dailyinterventions', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'interventionsTrendChart', 'width' => '400', 'height' => '250']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Success rate trend.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('successratetrend', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'successRateTrendChart', 'width' => '400', 'height' => '250']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Retention Analytics.
echo html_writer::tag('h3', get_string('retentionanalytics', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Retention rate.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('retentionrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $retention->retention_rate . '%', ['class' => 'display-3 text-success']);
echo html_writer::tag('p', $retention->active_students . ' / ' . $retention->total_students . ' ' .
    get_string('activestudents', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// At risk dropout.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('atriskdropout', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $retention->at_risk_dropout, ['class' => 'display-3 text-warning']);
echo html_writer::tag('p', get_string('students', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Dropout prediction.
echo html_writer::start_div('col-md-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('dropoutprediction', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $retention->dropout_prediction, ['class' => 'display-3 text-danger']);
echo html_writer::tag('p', get_string('highriskinactive', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Retention trend chart.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('retentiontrend', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'retentionTrendChart', 'width' => '800', 'height' => '300']);
echo html_writer::end_div();
echo html_writer::end_div();

// Supervisor Performance.
echo html_writer::tag('h3', get_string('supervisorperformance', 'local_student_monitor'), ['class' => 'mt-4']);

if (empty($supervisors)) {
    echo html_writer::div(
        get_string('nosupervisordata', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('supervisor', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('assignedstudents', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('interventions', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('avgresponse', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('studentsimproved', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('successrate', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach (array_slice($supervisors, 0, 10) as $supervisor) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', fullname($supervisor));
        echo html_writer::tag('td', $supervisor->assigned_students, ['class' => 'text-center']);
        echo html_writer::tag('td', $supervisor->total_interventions, ['class' => 'text-center']);
        echo html_writer::tag('td', round($supervisor->avg_response_hours, 1) . 'h', ['class' => 'text-center']);
        echo html_writer::tag('td', $supervisor->students_improved, ['class' => 'text-center']);

        $rateclass = 'success';
        if ($supervisor->success_rate < 30) {
            $rateclass = 'danger';
        } else if ($supervisor->success_rate < 60) {
            $rateclass = 'warning';
        }

        echo html_writer::tag('td',
            html_writer::tag('span', $supervisor->success_rate . '%', ['class' => 'badge badge-' . $rateclass]),
            ['class' => 'text-center']
        );
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

// Cohort Analysis.
echo html_writer::tag('h3', get_string('cohortanalysis', 'local_student_monitor'), ['class' => 'mt-4']);

// Cohort selector.
echo html_writer::start_div('mb-3');
$cohorturl = new moodle_url($PAGE->url);

$cohorttypes = ['course' => 'By Course', 'enrolment_date' => 'By Enrolment', 'risk_level' => 'By Risk Level'];
foreach ($cohorttypes as $type => $label) {
    $cohorturl->param('cohort', $type);
    $class = $cohortby === $type ? 'btn btn-primary' : 'btn btn-outline-primary';
    echo html_writer::link($cohorturl, $label, ['class' => $class . ' mr-2']);
}
echo html_writer::end_div();

if (empty($cohorts)) {
    echo html_writer::div(
        get_string('nocohortdata', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-sm']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('cohort', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('students', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('avgriskscore', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('critical', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('high', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('medium', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('low', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach (array_slice($cohorts, 0, 15) as $cohort) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', $cohort->cohort_name);
        echo html_writer::tag('td', $cohort->total_students, ['class' => 'text-center']);
        echo html_writer::tag('td', round($cohort->avg_risk_score, 1), ['class' => 'text-center']);
        echo html_writer::tag('td', $cohort->critical_count ?? 0, ['class' => 'text-center']);
        echo html_writer::tag('td', $cohort->high_count ?? 0, ['class' => 'text-center']);
        echo html_writer::tag('td', $cohort->medium_count ?? 0, ['class' => 'text-center']);
        echo html_writer::tag('td', $cohort->low_count ?? 0, ['class' => 'text-center']);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

// Export options.
echo html_writer::tag('h3', get_string('exportoptions', 'local_student_monitor'), ['class' => 'mt-4']);

$exporturl = new moodle_url('/local/student_monitor/export_bi_data.php', [
    'days' => $days,
    'cohort' => $cohortby,
    'sesskey' => sesskey()
]);

echo html_writer::link($exporturl, '📄 ' . get_string('exportexecutivesummary', 'local_student_monitor'),
    ['class' => 'btn btn-primary mb-3']);

// Back to dashboard.
$dashboardurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($dashboardurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary ml-2 mb-3']);

echo $OUTPUT->footer();
