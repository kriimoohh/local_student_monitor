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
 * Campaign statistics and analytics page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managesettings', context_system::instance());

$campaignid = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/campaign_stats.php', ['id' => $campaignid]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('campaignstatistics', 'local_student_monitor'));
$PAGE->set_heading(get_string('campaignstatistics', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->js_call_amd('local_student_monitor/campaign_charts', 'init', [$campaignid]);

$campaignmanager = new \local_student_monitor\manager\email_campaign_manager();

// Get campaign data.
$campaign = $DB->get_record('local_sm_campaigns', ['id' => $campaignid], '*', MUST_EXIST);
$stats = $campaignmanager->get_campaign_statistics($campaignid);

// Calculate metrics.
$openrate = $stats->total_sent > 0 ? round(($stats->total_opened / $stats->total_sent) * 100, 2) : 0;
$clickrate = $stats->total_sent > 0 ? round(($stats->total_clicked / $stats->total_sent) * 100, 2) : 0;
$conversionrate = $stats->total_sent > 0 ? round(($stats->total_converted / $stats->total_sent) * 100, 2) : 0;

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('campaignstatistics', 'local_student_monitor'));

// Campaign info.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h3', format_string($campaign->campaign_name), ['class' => 'card-title']);
echo html_writer::tag('p', '<strong>' . get_string('subject', 'local_student_monitor') . ':</strong> ' .
                       format_string($campaign->subject), ['class' => 'mb-1']);

$statusclass = [
    'draft' => 'badge-secondary',
    'scheduled' => 'badge-info',
    'sending' => 'badge-warning',
    'sent' => 'badge-success'
][$campaign->status] ?? 'badge-secondary';

echo html_writer::tag('p', '<strong>' . get_string('status', 'local_student_monitor') . ':</strong> ' .
                       html_writer::tag('span', get_string('status_' . $campaign->status, 'local_student_monitor'),
                       ['class' => 'badge ' . $statusclass]), ['class' => 'mb-1']);

if ($campaign->sent_time > 0) {
    echo html_writer::tag('p', '<strong>' . get_string('senttime', 'local_student_monitor') . ':</strong> ' .
                           userdate($campaign->sent_time, get_string('strftimedatetimeshort')), ['class' => 'mb-1']);
}

echo html_writer::end_div();
echo html_writer::end_div();

// Overall statistics KPIs.
echo html_writer::start_div('row mb-4');

// Total sent.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('totalsent', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_sent, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Open rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('openrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $openrate . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', $stats->total_opened . ' ' . get_string('opens', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Click rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('clickrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $clickrate . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', $stats->total_clicked . ' ' . get_string('clicks', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Conversion rate.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h5', get_string('conversionrate', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $conversionrate . '%', ['class' => 'kpi-number']);
echo html_writer::tag('small', $stats->total_converted . ' ' . get_string('conversions', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// A/B Testing Results.
if ($campaign->ab_testing && !empty($stats->variant_stats)) {
    echo html_writer::tag('h3', get_string('abtestingresults', 'local_student_monitor'), ['class' => 'mt-4']);

    echo html_writer::start_div('row mb-4');

    foreach ($stats->variant_stats as $variant => $variantstats) {
        $variantopenrate = $variantstats->sent > 0 ? round(($variantstats->opened / $variantstats->sent) * 100, 2) : 0;
        $variantclickrate = $variantstats->sent > 0 ? round(($variantstats->clicked / $variantstats->sent) * 100, 2) : 0;
        $variantconversionrate = $variantstats->sent > 0 ?
                                 round(($variantstats->converted / $variantstats->sent) * 100, 2) : 0;

        echo html_writer::start_div('col-md-6');
        echo html_writer::start_div('card mb-3');
        echo html_writer::start_div('card-body');
        echo html_writer::tag('h4', get_string('variant', 'local_student_monitor') . ' ' . strtoupper($variant),
                               ['class' => 'card-title']);

        echo html_writer::start_tag('table', ['class' => 'table table-sm']);
        echo html_writer::start_tag('tbody');

        // Sent.
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('sent', 'local_student_monitor'));
        echo html_writer::tag('td', $variantstats->sent, ['class' => 'text-right']);
        echo html_writer::end_tag('tr');

        // Open rate.
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('openrate', 'local_student_monitor'));
        echo html_writer::tag('td', $variantopenrate . '% (' . $variantstats->opened . ')', ['class' => 'text-right']);
        echo html_writer::end_tag('tr');

        // Click rate.
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('clickrate', 'local_student_monitor'));
        echo html_writer::tag('td', $variantclickrate . '% (' . $variantstats->clicked . ')', ['class' => 'text-right']);
        echo html_writer::end_tag('tr');

        // Conversion rate.
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('conversionrate', 'local_student_monitor'));
        echo html_writer::tag('td', $variantconversionrate . '% (' . $variantstats->converted . ')',
                               ['class' => 'text-right']);
        echo html_writer::end_tag('tr');

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');

        echo html_writer::end_div();
        echo html_writer::end_div();
        echo html_writer::end_div();
    }

    echo html_writer::end_div(); // Row.

    // Determine winner.
    if (count($stats->variant_stats) == 2) {
        $varianta = $stats->variant_stats['a'] ?? null;
        $variantb = $stats->variant_stats['b'] ?? null;

        if ($varianta && $variantb && $varianta->sent > 0 && $variantb->sent > 0) {
            $ratea = ($varianta->converted / $varianta->sent) * 100;
            $rateb = ($variantb->converted / $variantb->sent) * 100;

            $winner = $ratea > $rateb ? 'A' : ($rateb > $ratea ? 'B' : get_string('tie', 'local_student_monitor'));
            $winnerclass = $winner === 'A' || $winner === 'B' ? 'alert-success' : 'alert-info';

            echo html_writer::start_div('alert ' . $winnerclass);
            echo html_writer::tag('h5', get_string('winner', 'local_student_monitor') . ': ' . $winner);

            if ($winner === 'A' || $winner === 'B') {
                $difference = abs($ratea - $rateb);
                echo html_writer::tag('p', get_string('performancedifference', 'local_student_monitor',
                                       ['difference' => round($difference, 2)]));
            }

            echo html_writer::end_div();
        }
    }
}

// Charts.
echo html_writer::tag('h3', get_string('performancecharts', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Funnel chart.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('conversionfunnel', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'funnelChart', 'width' => '400', 'height' => '300']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// A/B comparison chart.
if ($campaign->ab_testing && !empty($stats->variant_stats)) {
    echo html_writer::start_div('col-md-6');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('abcomparison', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('canvas', '', ['id' => 'abComparisonChart', 'width' => '400', 'height' => '300']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div(); // Row.

// Recipient breakdown.
echo html_writer::tag('h3', get_string('recipientbreakdown', 'local_student_monitor'), ['class' => 'mt-4']);

// Get recipient details.
$recipients = $DB->get_records_sql("
    SELECT cr.id,
           u.firstname,
           u.lastname,
           u.email,
           cr.variant,
           cr.sent_time,
           cr.opened_time,
           cr.clicked_time,
           cr.converted_time
    FROM {local_sm_campaign_recipients} cr
    JOIN {user} u ON u.id = cr.userid
    WHERE cr.campaign_id = :campaignid
    ORDER BY cr.sent_time DESC
", ['campaignid' => $campaignid]);

if (!empty($recipients)) {
    echo html_writer::start_tag('table', ['class' => 'table table-striped table-sm']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('recipient', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('variant', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('sent', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('opened', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('clicked', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('converted', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($recipients as $recipient) {
        echo html_writer::start_tag('tr');

        // Recipient name.
        echo html_writer::start_tag('td');
        echo fullname($recipient) . '<br>';
        echo html_writer::tag('small', $recipient->email, ['class' => 'text-muted']);
        echo html_writer::end_tag('td');

        // Variant.
        echo html_writer::tag('td', $recipient->variant ? strtoupper($recipient->variant) : '-');

        // Sent.
        echo html_writer::tag('td', $recipient->sent_time > 0 ?
                               userdate($recipient->sent_time, get_string('strftimedatetimeshort')) : '-');

        // Opened.
        echo html_writer::start_tag('td');
        if ($recipient->opened_time > 0) {
            echo html_writer::tag('span', '✓', ['class' => 'badge badge-success']) . ' ' .
                 userdate($recipient->opened_time, get_string('strftimedatetimeshort'));
        } else {
            echo '-';
        }
        echo html_writer::end_tag('td');

        // Clicked.
        echo html_writer::start_tag('td');
        if ($recipient->clicked_time > 0) {
            echo html_writer::tag('span', '✓', ['class' => 'badge badge-success']) . ' ' .
                 userdate($recipient->clicked_time, get_string('strftimedatetimeshort'));
        } else {
            echo '-';
        }
        echo html_writer::end_tag('td');

        // Converted.
        echo html_writer::start_tag('td');
        if ($recipient->converted_time > 0) {
            echo html_writer::tag('span', '✓', ['class' => 'badge badge-success']) . ' ' .
                 userdate($recipient->converted_time, get_string('strftimedatetimeshort'));
        } else {
            echo '-';
        }
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
} else {
    echo html_writer::div(
        get_string('norecipients', 'local_student_monitor'),
        'alert alert-info'
    );
}

// Export options.
echo html_writer::tag('h3', get_string('exportoptions', 'local_student_monitor'), ['class' => 'mt-4']);

$exporturl = new moodle_url('/local/student_monitor/export_campaign.php', [
    'id' => $campaignid,
    'format' => 'csv',
    'sesskey' => sesskey()
]);

echo html_writer::link($exporturl, get_string('exporttocsv', 'local_student_monitor'),
    ['class' => 'btn btn-primary mb-3']);

// Back to campaigns.
$backurl = new moodle_url('/local/student_monitor/campaigns.php');
echo html_writer::link($backurl, get_string('backtocampaigns', 'local_student_monitor'),
    ['class' => 'btn btn-secondary ml-2 mb-3']);

echo $OUTPUT->footer();
