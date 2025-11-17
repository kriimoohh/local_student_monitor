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
 * Peer comparison page - anonymous performance comparison.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$PAGE->set_url(new moodle_url('/local/student_monitor/peer_comparison.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('peercomparison', 'local_student_monitor'));
$PAGE->set_heading(get_string('peercomparison', 'local_student_monitor'));
$PAGE->set_pagelayout('standard');

$PAGE->requires->js_call_amd('local_student_monitor/peer_comparison_chart', 'init');

$peercomparison = new \local_student_monitor\manager\peer_comparison();
$comparison = $peercomparison->get_peer_comparison($USER->id);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('peercomparison', 'local_student_monitor'));
echo html_writer::tag('p', get_string('peercomparison_desc', 'local_student_monitor'));

// Performance category.
echo html_writer::start_div('alert alert-info mb-4');
echo html_writer::tag('h4', get_string('yourperformance', 'local_student_monitor'));

$categoryicons = [
    'top' => '🌟',
    'above_average' => '👍',
    'average' => '✅',
    'below_average' => '📊',
    'needs_improvement' => '💪'
];

$categoryclass = [
    'top' => 'success',
    'above_average' => 'primary',
    'average' => 'info',
    'below_average' => 'warning',
    'needs_improvement' => 'danger'
][$comparison->category] ?? 'info';

echo html_writer::tag('p',
    $categoryicons[$comparison->category] . ' ' .
    get_string('category_' . $comparison->category, 'local_student_monitor') . ' ' .
    html_writer::tag('span', $comparison->overall_percentile . get_string('percentile', 'local_student_monitor'),
        ['class' => 'badge badge-' . $categoryclass . ' ml-2']),
    ['class' => 'lead']
);

echo html_writer::tag('p', get_string('comparedto', 'local_student_monitor', $comparison->peer_count),
    ['class' => 'text-muted']);
echo html_writer::end_div();

// Radar chart.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('performanceradar', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'peerComparisonRadar', 'width' => '400', 'height' => '400']);

// Pass data to JavaScript.
$chartdata = $peercomparison->get_comparison_chart_data($comparison);
$PAGE->requires->js_init_call('M.local_student_monitor.init_radar_chart', [
    json_encode($chartdata)
], false);

echo html_writer::end_div();
echo html_writer::end_div();

// Detailed metrics.
echo html_writer->tag('h3', get_string('detailedmetrics', 'local_student_monitor'), ['class' => 'mt-4']);

$metrics = [
    [
        'title' => get_string('loginfrequency', 'local_student_monitor'),
        'data' => $comparison->login_frequency,
        'unit' => get_string('logins', 'local_student_monitor'),
        'icon' => '🔑'
    ],
    [
        'title' => get_string('assignmentcompletion', 'local_student_monitor'),
        'data' => $comparison->assignment_completion,
        'unit' => '%',
        'icon' => '📝'
    ],
    [
        'title' => get_string('engagement', 'local_student_monitor'),
        'data' => $comparison->engagement_score,
        'unit' => get_string('activities', 'local_student_monitor'),
        'icon' => '💡'
    ],
    [
        'title' => get_string('gradeperformance', 'local_student_monitor'),
        'data' => $comparison->grade_performance,
        'unit' => '%',
        'icon' => '📊'
    ]
];

echo html_writer::start_div('row');

foreach ($metrics as $metric) {
    echo html_writer::start_div('col-md-6 mb-4');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body');

    echo html_writer::tag('h5', $metric['icon'] . ' ' . $metric['title'], ['class' => 'card-title']);

    echo html_writer::start_div('row');

    // Your value.
    echo html_writer::start_div('col-6 text-center');
    echo html_writer::tag('small', get_string('yourvalue', 'local_student_monitor'),
        ['class' => 'd-block text-muted']);
    echo html_writer::tag('div', $metric['data']->user_value . ' ' . $metric['unit'],
        ['class' => 'display-4 text-primary']);
    echo html_writer::end_div();

    // Peer average.
    echo html_writer::start_div('col-6 text-center');
    echo html_writer::tag('small', get_string('peeraverage', 'local_student_monitor'),
        ['class' => 'd-block text-muted']);
    echo html_writer::tag('div', $metric['data']->peer_avg . ' ' . $metric['unit'],
        ['class' => 'display-4 text-secondary']);
    echo html_writer::end_div();

    echo html_writer::end_div(); // Row.

    // Percentile bar.
    echo html_writer::tag('small', get_string('percentileposition', 'local_student_monitor'),
        ['class' => 'd-block mt-3 text-muted']);

    $percentileclass = 'success';
    if ($metric['data']->percentile < 25) {
        $percentileclass = 'danger';
    } else if ($metric['data']->percentile < 50) {
        $percentileclass = 'warning';
    } else if ($metric['data']->percentile < 75) {
        $percentileclass = 'info';
    }

    echo html_writer::start_div('progress', ['style' => 'height: 30px;']);
    echo html_writer::div(
        $metric['data']->percentile . get_string('percentile', 'local_student_monitor'),
        'progress-bar bg-' . $percentileclass,
        ['style' => 'width: ' . $metric['data']->percentile . '%', 'role' => 'progressbar']
    );
    echo html_writer::end_div();

    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div(); // Row.

// Insights and recommendations.
echo html_writer::tag('h3', get_string('insights', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('alert alert-light');

if ($comparison->overall_percentile >= 75) {
    echo html_writer::tag('p', '🎉 ' . get_string('insight_top_performer', 'local_student_monitor'));
} else if ($comparison->overall_percentile >= 50) {
    echo html_writer::tag('p', '👍 ' . get_string('insight_above_average', 'local_student_monitor'));
} else if ($comparison->overall_percentile >= 25) {
    echo html_writer::tag('p', '💪 ' . get_string('insight_room_for_improvement', 'local_student_monitor'));
} else {
    echo html_writer::tag('p', '🚀 ' . get_string('insight_needs_boost', 'local_student_monitor'));
}

// Specific recommendations based on weakest area.
$weakest = 'engagement';
$lowestpercentile = 100;

foreach ([$comparison->login_frequency, $comparison->assignment_completion,
          $comparison->engagement_score, $comparison->grade_performance] as $i => $data) {
    if ($data->percentile < $lowestpercentile) {
        $lowestpercentile = $data->percentile;
        $weakest = ['login', 'assignment', 'engagement', 'grade'][$i];
    }
}

echo html_writer::tag('p', get_string('improvement_suggestion_' . $weakest, 'local_student_monitor'));

echo html_writer::end_div();

// Privacy note.
echo html_writer::div(
    '🔒 ' . get_string('privacy_note', 'local_student_monitor'),
    'alert alert-secondary mt-4'
);

// Back to dashboard.
$dashboardurl = new moodle_url('/local/student_monitor/student_dashboard.php');
echo html_writer::link($dashboardurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-primary mt-3']);

echo $OUTPUT->footer();
