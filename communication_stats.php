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
 * Communication statistics page with SMS cost tracking.
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

$PAGE->set_url(new moodle_url('/local/student_monitor/communication_stats.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('communicationstats', 'local_student_monitor'));
$PAGE->set_heading(get_string('communicationstats', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Get SMS cost tracker.
$smstracker = new \local_student_monitor\manager\sms_cost_tracker();

// Calculate date range based on period.
switch ($period) {
    case 'week':
        $startdate = strtotime('monday this week');
        $enddate = time();
        break;
    case 'month':
        $startdate = strtotime('first day of this month');
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

// Get statistics.
$smsstats = $smstracker->get_cost_statistics($startdate, $enddate);
$dailycosts = $smstracker->get_daily_costs(30);
$costbytype = $smstracker->get_cost_by_type($startdate, $enddate);

// Get notification statistics by channel.
$channelstats = $DB->get_records_sql("
    SELECT
        CASE
            WHEN channels LIKE '%email%' THEN 'Email'
            WHEN channels LIKE '%moodle%' THEN 'Moodle'
            WHEN channels LIKE '%sms%' THEN 'SMS'
            WHEN channels LIKE '%whatsapp%' THEN 'WhatsApp'
        END as channel,
        COUNT(*) as count
    FROM {local_sm_notifications}
    WHERE timecreated >= :startdate AND timecreated <= :enddate
    GROUP BY channel
", ['startdate' => $startdate, 'enddate' => $enddate]);

// Prepare chart data for daily costs.
$chartlabels = [];
$chartdata = [];
foreach ($dailycosts as $day) {
    $chartlabels[] = userdate($day['date'], get_string('strftimedayshort'));
    $chartdata[] = $day['total_cost'];
}

$costchartdata = [
    'labels' => $chartlabels,
    'data' => $chartdata,
    'title' => get_string('dailysmscosts', 'local_student_monitor'),
    'currency' => $smsstats->currency
];

// Initialize JavaScript for charts.
$PAGE->requires->js_call_amd('local_student_monitor/communication_charts', 'init', [$costchartdata]);

echo $OUTPUT->header();

echo html_writer::tag('h2', '📊 ' . get_string('communicationstats', 'local_student_monitor'), ['class' => 'sm-page-title']);

// Period selector.
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', get_string('period', 'local_student_monitor') . ': ', ['for' => 'period-select']);
echo html_writer::start_tag('select', [
    'id' => 'period-select',
    'class' => 'custom-select',
    'onchange' => 'window.location.href="?period=" + this.value'
]);
echo html_writer::tag('option', get_string('thisweek', 'local_student_monitor'),
    ['value' => 'week', 'selected' => ($period === 'week')]);
echo html_writer::tag('option', get_string('thismonth', 'local_student_monitor'),
    ['value' => 'month', 'selected' => ($period === 'month')]);
echo html_writer::tag('option', get_string('thisyear', 'local_student_monitor'),
    ['value' => 'year', 'selected' => ($period === 'year')]);
echo html_writer::end_tag('select');
echo html_writer::end_div();

// SMS Cost KPIs.
echo html_writer::start_div('row mb-4');

// Total SMS sent.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalsmssent', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $smsstats->sms_count, ['class' => 'kpi-number']);
echo html_writer::tag('small', $smsstats->total_parts . ' ' . get_string('parts', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Total cost.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-danger text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalcost', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', number_format($smsstats->total_cost, 0) . ' ' . $smsstats->currency, ['class' => 'kpi-number']);
echo html_writer::tag('small', get_string('currentperiod', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Average cost per SMS.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('avgcostpersms', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', number_format($smsstats->avg_cost, 0) . ' ' . $smsstats->currency, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Budget status.
$monthlybudget = (float)get_config('local_student_monitor', 'sms_monthly_budget');
$monthlytotal = $smstracker->get_monthly_total();
$budgetpercentage = $monthlybudget > 0 ? round(($monthlytotal / $monthlybudget) * 100, 1) : 0;

echo html_writer::start_div('col-md-3');
$budgetclass = $budgetpercentage >= 90 ? 'bg-warning' : 'bg-success';
echo html_writer::start_div('card kpi-card ' . $budgetclass . ' text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('monthlybudget', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $budgetpercentage . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', number_format($monthlytotal, 0) . ' / ' . number_format($monthlybudget, 0) . ' ' . $smsstats->currency);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Charts.
echo html_writer::start_div('row mt-4');

// Daily costs chart.
echo html_writer::start_div('col-md-12 mb-4');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::start_div('chart-container', ['style' => 'position: relative; height: 300px;']);
echo html_writer::tag('canvas', '', ['id' => 'dailyCostsChart']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Cost breakdown by type.
echo html_writer::start_div('row mt-4');
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h4', get_string('costbytype', 'local_student_monitor'));

if (empty($costbytype)) {
    echo html_writer::tag('p', get_string('nodata', 'local_student_monitor'), ['class' => 'text-muted']);
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('notificationtype', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('count', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($costbytype as $type) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string($type->notification_type, 'local_student_monitor'));
        echo html_writer::tag('td', $type->notification_count);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

echo html_writer::end_div(); // Col.

// Channel distribution.
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h4', get_string('channeldistribution', 'local_student_monitor'));

if (empty($channelstats)) {
    echo html_writer::tag('p', get_string('nodata', 'local_student_monitor'), ['class' => 'text-muted']);
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('channel', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('count', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($channelstats as $stat) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', $stat->channel);
        echo html_writer::tag('td', $stat->count);
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

echo html_writer::end_div(); // Col.
echo html_writer::end_div(); // Row.

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
