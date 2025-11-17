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
 * Email campaigns management page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managesettings', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHA);
$campaignid = optional_param('campaignid', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/campaigns.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('emailcampaigns', 'local_student_monitor'));
$PAGE->set_heading(get_string('emailcampaigns', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

$campaignmanager = new \local_student_monitor\manager\email_campaign_manager();

// Handle actions.
if ($action && confirm_sesskey()) {
    switch ($action) {
        case 'send':
            if ($campaignid) {
                $stats = $campaignmanager->send_campaign($campaignid);
                $message = get_string('campaignsent', 'local_student_monitor', [
                    'sent' => $stats->sent,
                    'failed' => $stats->failed
                ]);
                redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;

        case 'delete':
            if ($campaignid) {
                $campaignmanager->delete_campaign($campaignid);
                redirect($PAGE->url, get_string('campaigndeleted', 'local_student_monitor'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
    }
}

// Get campaigns.
$campaigns = $campaignmanager->get_campaigns();

// Calculate overall statistics.
$totalcampaigns = count($campaigns);
$totalsent = 0;
$draftcount = 0;
$scheduledcount = 0;

foreach ($campaigns as $campaign) {
    if ($campaign->status === 'sent') {
        $totalsent++;
    } else if ($campaign->status === 'draft') {
        $draftcount++;
    } else if ($campaign->status === 'scheduled') {
        $scheduledcount++;
    }
}

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('emailcampaigns', 'local_student_monitor'));

// Statistics KPIs.
echo html_writer::start_div('row mb-4');

// Total campaigns.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-info text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('totalcampaigns', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $totalcampaigns, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Campaigns sent.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-success text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('campaignssent', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $totalsent, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Drafts.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-warning text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('drafts', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $draftcount, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Scheduled.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card kpi-card bg-primary text-white');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('scheduled', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $scheduledcount, ['class' => 'kpi-number']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Create new campaign button.
$newurl = new moodle_url('/local/student_monitor/campaign_create.php');
echo html_writer::link($newurl, get_string('createnewcampaign', 'local_student_monitor'),
    ['class' => 'btn btn-primary mb-3']);

// Campaigns table.
if (empty($campaigns)) {
    echo html_writer::div(
        get_string('nocampaigns', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('campaignname', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('subject', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('status', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('recipients', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('scheduledtime', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('abtesting', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($campaigns as $campaign) {
        echo html_writer::start_tag('tr');

        // Campaign name.
        echo html_writer::tag('td', $campaign->campaign_name);

        // Subject.
        echo html_writer::tag('td', format_string($campaign->subject));

        // Status badge.
        $statusclass = [
            'draft' => 'badge-secondary',
            'scheduled' => 'badge-info',
            'sending' => 'badge-warning',
            'sent' => 'badge-success'
        ][$campaign->status] ?? 'badge-secondary';

        echo html_writer::start_tag('td');
        echo html_writer::tag('span', get_string('status_' . $campaign->status, 'local_student_monitor'),
            ['class' => 'badge ' . $statusclass]);
        echo html_writer::end_tag('td');

        // Recipients.
        echo html_writer::tag('td', $campaign->recipients_count ?? '-');

        // Scheduled time.
        echo html_writer::start_tag('td');
        if ($campaign->scheduled_time > 0) {
            echo userdate($campaign->scheduled_time, get_string('strftimedatetimeshort'));
        } else {
            echo '-';
        }
        echo html_writer::end_tag('td');

        // A/B testing.
        echo html_writer::tag('td', $campaign->ab_testing ? get_string('yes') : get_string('no'));

        // Actions.
        echo html_writer::start_tag('td');

        if ($campaign->status === 'draft' || $campaign->status === 'scheduled') {
            $sendurl = new moodle_url($PAGE->url, [
                'action' => 'send',
                'campaignid' => $campaign->id,
                'sesskey' => sesskey()
            ]);
            echo html_writer::link($sendurl, get_string('send', 'local_student_monitor'),
                ['class' => 'btn btn-sm btn-success']);
        }

        if ($campaign->status === 'sent') {
            $statsurl = new moodle_url('/local/student_monitor/campaign_stats.php', ['id' => $campaign->id]);
            echo html_writer::link($statsurl, get_string('viewstats', 'local_student_monitor'),
                ['class' => 'btn btn-sm btn-info ml-1']);
        }

        $deleteurl = new moodle_url($PAGE->url, [
            'action' => 'delete',
            'campaignid' => $campaign->id,
            'sesskey' => sesskey()
        ]);
        echo html_writer::link($deleteurl, get_string('delete'),
            ['class' => 'btn btn-sm btn-danger ml-1', 'onclick' => 'return confirm("' . get_string('confirmdeletecampaign', 'local_student_monitor') . '")']);

        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

// Back to dashboard.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
