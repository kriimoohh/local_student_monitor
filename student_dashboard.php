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
 * Student self-service dashboard.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$PAGE->set_url(new moodle_url('/local/student_monitor/student_dashboard.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('studentdashboard', 'local_student_monitor'));
$PAGE->set_heading(get_string('studentdashboard', 'local_student_monitor'));
$PAGE->set_pagelayout('standard');

$PAGE->requires->js_call_amd('local_student_monitor/student_dashboard', 'init');

// Managers.
$recommendationengine = new \local_student_monitor\manager\recommendation_engine();
$gamificationmanager = new \local_student_monitor\manager\gamification_manager();

// Get student data.
$tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $USER->id]);
$gamificationstats = $gamificationmanager->get_user_gamification_stats($USER->id);

// Generate AI recommendations.
$recommendations = $recommendationengine->generate_recommendations($USER->id, 5);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('welcomeback', 'local_student_monitor', fullname($USER)));

// Personal stats overview.
echo html_writer::start_div('row mb-4');

// Risk level card.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body text-center');

if ($tracking) {
    $risklevel = $tracking->risk_level;
    $riskclass = [
        'FAIBLE' => 'success',
        'MOYEN' => 'warning',
        'ÉLEVÉ' => 'danger',
        'CRITIQUE' => 'dark'
    ][$risklevel] ?? 'info';

    echo html_writer::tag('h5', get_string('yourrisk', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', html_writer::tag('span', $risklevel,
        ['class' => 'badge badge-' . $riskclass . ' badge-lg']), ['class' => 'mb-2']);
    // Normalize risk level for translation (remove accents).
    $riskkey = strtolower(str_replace(['É', 'è', 'é', 'ê'], 'e', $risklevel));
    echo html_writer::tag('small', get_string('riskexplanation_' . $riskkey, 'local_student_monitor'),
        ['class' => 'text-muted d-block']);
} else {
    echo html_writer::tag('h5', get_string('yourrisk', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', get_string('noriskdata', 'local_student_monitor'), ['class' => 'text-muted']);
}

echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Gamification stats.
if ($gamificationstats) {
    // Points.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card bg-primary text-white');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('yourpoints', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', $gamificationstats->total_points, ['class' => 'display-4']);
    echo html_writer::tag('small', get_string('level', 'local_student_monitor') . ' ' . $gamificationstats->level);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Streak.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card bg-warning text-white');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('yourstreak', 'local_student_monitor'), ['class' => 'card-title']);
    echo html_writer::tag('div', '🔥 ' . $gamificationstats->current_streak, ['class' => 'display-4']);
    echo html_writer::tag('small', get_string('days', 'local_student_monitor'));
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Assignments.
    echo html_writer::start_div('col-md-3');
    echo html_writer::start_div('card bg-info text-white');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('assignments', 'local_student_monitor'), ['class' => 'card-title');
    if ($tracking) {
        echo html_writer::tag('div', $tracking->missing_assignments, ['class' => 'display-4']);
        echo html_writer::tag('small', get_string('missing', 'local_student_monitor'));
    } else {
        echo html_writer::tag('div', '0', ['class' => 'display-4']);
    }
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div(); // Row.

// AI Recommendations section.
echo html_writer::tag('h3', '🤖 ' . get_string('personalizedrecommendations', 'local_student_monitor'),
    ['class' => 'mt-4']);

if (empty($recommendations)) {
    echo html_writer::div(
        get_string('norecommendations', 'local_student_monitor'),
        'alert alert-success'
    );
    echo html_writer::tag('p', get_string('keepupgoodwork', 'local_student_monitor'));
} else {
    echo html_writer::start_div('list-group mb-4');

    foreach ($recommendations as $rec) {
        $priorityclass = [
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'secondary'
        ][$rec->priority] ?? 'secondary';

        echo html_writer::start_div('list-group-item list-group-item-action border-left-' . $priorityclass);

        echo html_writer::start_div('d-flex w-100 justify-content-between');
        echo html_writer::tag('h5', $rec->icon . ' ' . $rec->title, ['class' => 'mb-1']);
        echo html_writer::tag('small', get_string('impact', 'local_student_monitor') . ': ' .
            $rec->impact_score . '%', ['class' => 'badge badge-' . $priorityclass]);
        echo html_writer::end_div();

        echo html_writer::tag('p', $rec->description, ['class' => 'mb-1']);

        if ($rec->action_url) {
            echo html_writer::link($rec->action_url, get_string('takeaction', 'local_student_monitor'),
                ['class' => 'btn btn-sm btn-primary mt-2']);
        }

        echo html_writer::end_div();
    }

    echo html_writer::end_div();
}

// Progress overview.
echo html_writer::tag('h3', '📈 ' . get_string('yourprogress', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// Activity this week.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('activitythisweek', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'weeklyActivityChart', 'width' => '400', 'height' => '200']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Performance trend.
echo html_writer::start_div('col-md-6');
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('performancetrend', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('canvas', '', ['id' => 'performanceTrendChart', 'width' => '400', 'height' => '200']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Quick actions.
echo html_writer::tag('h3', '⚡ ' . get_string('quickactions', 'local_student_monitor'), ['class' => 'mt-4']);

echo html_writer::start_div('row mb-4');

// View leaderboard.
echo html_writer::start_div('col-md-4');
$leaderboardurl = new moodle_url('/local/student_monitor/leaderboard.php');
echo html_writer::start_div('card bg-light h-100');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('div', '🏆', ['class' => 'display-3']);
echo html_writer::tag('h5', get_string('viewleaderboard', 'local_student_monitor'), ['class' => 'card-title mt-3']);
echo html_writer::link($leaderboardurl, get_string('goto', 'local_student_monitor'),
    ['class' => 'btn btn-primary btn-block mt-3']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// View calendar.
echo html_writer::start_div('col-md-4');
$calendarurl = new moodle_url('/calendar/view.php');
echo html_writer::start_div('card bg-light h-100');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('div', '📅', ['class' => 'display-3']);
echo html_writer::tag('h5', get_string('viewcalendar', 'local_student_monitor'), ['class' => 'card-title mt-3']);
echo html_writer::link($calendarurl, get_string('goto', 'local_student_monitor'),
    ['class' => 'btn btn-primary btn-block mt-3']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// View courses.
echo html_writer::start_div('col-md-4');
$coursesurl = new moodle_url('/my/courses.php');
echo html_writer::start_div('card bg-light h-100');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('div', '📚', ['class' => 'display-3']);
echo html_writer::tag('h5', get_string('viewcourses', 'local_student_monitor'), ['class' => 'card-title mt-3']);
echo html_writer::link($coursesurl, get_string('goto', 'local_student_monitor'),
    ['class' => 'btn btn-primary btn-block mt-3']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // Row.

// Recent achievements.
if ($gamificationstats) {
    echo html_writer::tag('h3', '🎖️ ' . get_string('recentachievements', 'local_student_monitor'),
        ['class' => 'mt-4']);

    $recentachievements = $DB->get_records('local_sm_achievements', ['userid' => $USER->id],
        'timecreated DESC', '*', 0, 5);

    if (empty($recentachievements)) {
        echo html_writer::div(
            get_string('noachievementsyet', 'local_student_monitor'),
            'alert alert-info'
        );
    } else {
        echo html_writer::start_div('row');

        foreach ($recentachievements as $achievement) {
            $achievementdata = \local_student_monitor\manager\gamification_manager::ACHIEVEMENTS[$achievement->achievement_key] ?? null;

            if ($achievementdata) {
                echo html_writer::start_div('col-md-6 mb-3');
                echo html_writer::start_div('card');
                echo html_writer::start_div('card-body');
                echo html_writer::tag('div', $achievementdata['badge'], ['class' => 'display-4 text-center']);
                echo html_writer::tag('h6', get_string('achievement_' . $achievement->achievement_key, 'local_student_monitor'),
                    ['class' => 'card-title text-center mt-2']);
                echo html_writer::tag('p', '+' . $achievement->points_awarded . ' ' . get_string('points', 'local_student_monitor'),
                    ['class' => 'text-center text-muted']);
                echo html_writer::tag('small', userdate($achievement->timecreated, get_string('strftimedatetimeshort')),
                    ['class' => 'd-block text-center text-muted']);
                echo html_writer::end_div();
                echo html_writer::end_div();
                echo html_writer::end_div();
            }
        }

        echo html_writer::end_div();
    }
}

// Tips and motivation.
echo html_writer::tag('h3', '💡 ' . get_string('tipsandmotivation', 'local_student_monitor'), ['class' => 'mt-4']);

$tips = [
    get_string('tip1', 'local_student_monitor'),
    get_string('tip2', 'local_student_monitor'),
    get_string('tip3', 'local_student_monitor'),
    get_string('tip4', 'local_student_monitor'),
    get_string('tip5', 'local_student_monitor')
];

$randomtip = $tips[array_rand($tips)];

echo html_writer::div($randomtip, 'alert alert-info');

echo $OUTPUT->footer();
