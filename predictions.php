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
 * Predictive analytics and early warnings page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:viewreports', context_system::instance());

$daysahead = optional_param('days', 7, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/predictions.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('predictiveanalytics', 'local_student_monitor'));
$PAGE->set_heading(get_string('predictiveanalytics', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Handle actions.
if ($action === 'preview' && $userid && confirm_sesskey()) {
    $predictor = new \local_student_monitor\manager\predictive_analytics();
    $prediction = $predictor->predict_risk($userid, $daysahead);

    // Return JSON for AJAX.
    header('Content-Type: application/json');
    echo json_encode($prediction);
    exit;
}

// Get predictive analytics.
$predictor = new \local_student_monitor\manager\predictive_analytics();
$report = $predictor->generate_prediction_report($daysahead);

// Prepare chart data for risk distribution.
$riskchartdata = [
    'labels' => [
        get_string('risk_critique', 'local_student_monitor'),
        get_string('risk_eleve', 'local_student_monitor'),
        get_string('risk_moyen', 'local_student_monitor'),
        get_string('risk_faible', 'local_student_monitor')
    ],
    'data' => [
        $report->risk_distribution['CRITICAL'],
        $report->risk_distribution['HIGH'],
        $report->risk_distribution['MEDIUM'],
        $report->risk_distribution['LOW']
    ],
    'colors' => ['#dc3545', '#fd7e14', '#ffc107', '#28a745']
];

// Prepare trend chart data.
$trendchartdata = [
    'labels' => [
        get_string('deteriorating', 'local_student_monitor'),
        get_string('stable', 'local_student_monitor'),
        get_string('improving', 'local_student_monitor')
    ],
    'data' => [
        $report->trend_summary['deteriorating'],
        $report->trend_summary['stable'],
        $report->trend_summary['improving']
    ],
    'colors' => ['#dc3545', '#ffc107', '#28a745']
];

// Initialize JavaScript.
$PAGE->requires->js_call_amd('local_student_monitor/predictions', 'init', [
    $riskchartdata,
    $trendchartdata
]);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('predictiveanalytics', 'local_student_monitor'));

// Days ahead selector.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('predictionhorizon', 'local_student_monitor') . ': ', ['for' => 'days-select']);
echo html_writer::start_tag('select', [
    'id' => 'days-select',
    'class' => 'custom-select',
    'onchange' => 'window.location.href="?days=" + this.value'
]);
echo html_writer::tag('option', '3 ' . get_string('days', 'local_student_monitor'),
    ['value' => '3', 'selected' => ($daysahead === 3)]);
echo html_writer::tag('option', '7 ' . get_string('days', 'local_student_monitor'),
    ['value' => '7', 'selected' => ($daysahead === 7)]);
echo html_writer::tag('option', '14 ' . get_string('days', 'local_student_monitor'),
    ['value' => '14', 'selected' => ($daysahead === 14)]);
echo html_writer::tag('option', '30 ' . get_string('days', 'local_student_monitor'),
    ['value' => '30', 'selected' => ($daysahead === 30)]);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// Prediction summary KPIs.
echo html_writer::start_div('row mb-4');

// Total predictions.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalpredictions', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $report->total_students, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('students', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Early warnings.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('earlywarnings', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $report->early_warnings, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('atriskpredicted', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Average confidence.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('avgconfidence', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $report->avg_confidence . '%', ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Deteriorating trend.
echo html_writer::start_div('col-md-3');
$deterioratingpct = $report->total_students > 0
    ? round(($report->trend_summary['deteriorating'] / $report->total_students) * 100, 1)
    : 0;
echo html_writer::start_div('card kpi-card bg-danger text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('deterioratingtrend', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $report->trend_summary['deteriorating'], ['class' => 'kpi-number']);
echo html_writer::tag('small', $deterioratingpct . '% ' . get_string('ofstudents', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Charts.
echo html_writer::start_div('row mt-4 mb-4');

// Predicted risk distribution.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('predictedriskdistribution', 'local_student_monitor'));
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'riskChart']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Trend direction.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', get_string('trenddirection', 'local_student_monitor'));
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'trendChart']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Early warnings table.
if (!empty($report->warnings)) {
    echo html_writer::tag('h3', get_string('earlywarnings', 'local_student_monitor'), ['class' => 'mt-4']);

    echo html_writer::start_tag('table', ['class' => 'table table-striped table-hover']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('student', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('currentrisk', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('predictedrisk', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('confidence', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('probability', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('trend', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('keyfactors', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($report->warnings as $warning) {
        echo html_writer::start_tag('tr');

        // Student name.
        echo html_writer::start_tag('td');
        $studenturl = new moodle_url('/user/profile.php', ['id' => $warning->userid]);
        echo html_writer::link($studenturl, $warning->fullname);
        echo html_writer::tag('br');
        echo html_writer::tag('small', $warning->email, ['class' => 'text-muted']);
        echo html_writer::end_tag('td');

        // Current risk.
        $currentclass = [
            'CRITICAL' => 'badge-danger',
            'HIGH' => 'badge-warning',
            'MEDIUM' => 'badge-info',
            'LOW' => 'badge-success'
        ][$warning->current_risk] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', $warning->current_risk, ['class' => 'badge ' . $currentclass]);
        echo html_writer::end_tag('td');

        // Predicted risk.
        $predictedclass = [
            'CRITICAL' => 'badge-danger',
            'HIGH' => 'badge-warning',
            'MEDIUM' => 'badge-info',
            'LOW' => 'badge-success'
        ][$warning->predicted_risk] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', $warning->predicted_risk, ['class' => 'badge ' . $predictedclass]);
        echo html_writer::end_tag('td');

        // Confidence.
        echo html_writer::tag('td', $warning->confidence . '%');

        // Probability.
        echo html_writer::tag('td', $warning->probability . '%');

        // Trend.
        $trendicon = [
            'deteriorating' => '↓',
            'improving' => '↑',
            'stable' => '→'
        ][$warning->trend_direction] ?? '?';

        $trendcolor = [
            'deteriorating' => 'text-danger',
            'improving' => 'text-success',
            'stable' => 'text-warning'
        ][$warning->trend_direction] ?? '';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', $trendicon . ' ' . get_string($warning->trend_direction, 'local_student_monitor'),
            ['class' => $trendcolor . ' font-weight-bold']);
        echo html_writer::end_tag('td');

        // Key factors.
        echo html_writer::start_tag('td');
        foreach (array_slice($warning->factors, 0, 2) as $factor) {
            echo html_writer::tag('small', '• ' . $factor['factor'], ['class' => 'd-block']);
        }
        echo html_writer::end_tag('td');

        // Actions.
        echo html_writer::start_tag('td');
        $detailsurl = new moodle_url('/local/student_monitor/student_detail.php', ['userid' => $warning->userid]);
        echo html_writer::link($detailsurl, get_string('viewdetails', 'local_student_monitor'),
            ['class' => 'btn btn-sm btn-info']);
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
} else {
    echo html_writer::div(
        get_string('noearlywarnings', 'local_student_monitor'),
        'alert alert-success mt-4'
    );
}

// Prediction details.
echo html_writer::start_div('mt-4');
echo html_writer::tag('h4', get_string('predictiondetails', 'local_student_monitor'));
echo html_writer::start_tag('ul');
echo html_writer::tag('li', get_string('predictionhorizoninfo', 'local_student_monitor', $daysahead));
echo html_writer::tag('li', get_string('predictiondateinfo', 'local_student_monitor',
    userdate($report->prediction_date, get_string('strftimedatefullshort'))));
echo html_writer::tag('li', get_string('predictionmethodinfo', 'local_student_monitor'));
echo html_writer::tag('li', get_string('predictionconfidenceinfo', 'local_student_monitor'));
echo html_writer::end_tag('ul');
echo html_writer::end_div();

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
