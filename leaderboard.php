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
 * Gamification leaderboard page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$period = optional_param('period', 'all', PARAM_ALPHA);
$limit = optional_param('limit', 50, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/student_monitor/leaderboard.php', [
    'period' => $period,
    'limit' => $limit
]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('leaderboard', 'local_student_monitor'));
$PAGE->set_heading(get_string('leaderboard', 'local_student_monitor'));
$PAGE->set_pagelayout('standard');

$gamificationmanager = new \local_student_monitor\manager\gamification_manager();

// Get leaderboard data.
$leaderboard = $gamificationmanager->get_leaderboard($limit, $period);

// Get current user's stats.
$userstats = $gamificationmanager->get_user_gamification_stats($USER->id);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('leaderboard', 'local_student_monitor'));

// Period selector.
echo html_writer::start_div('mb-3');
$periodurl = new moodle_url($PAGE->url);

$periods = ['all', 'month', 'week'];
foreach ($periods as $p) {
    $periodurl->param('period', $p);
    $class = $period === $p ? 'btn btn-primary' : 'btn btn-outline-primary';
    echo html_writer::link($periodurl, get_string('period_' . $p, 'local_student_monitor'),
        ['class' => $class . ' mr-2']);
}
echo html_writer::end_div();

// Current user stats card.
if ($userstats) {
    echo html_writer::start_div('card mb-4 bg-light');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h4', get_string('yourstats', 'local_student_monitor'), ['class' => 'card-title']);

    echo html_writer::start_div('row');

    // Points.
    echo html_writer::start_div('col-md-3 text-center');
    echo html_writer::tag('div', $userstats->total_points, ['class' => 'display-4 text-primary']);
    echo html_writer::tag('div', get_string('points', 'local_student_monitor'), ['class' => 'text-muted']);
    echo html_writer::end_div();

    // Level.
    echo html_writer::start_div('col-md-3 text-center');
    echo html_writer::tag('div', $userstats->level, ['class' => 'display-4 text-success']);
    echo html_writer::tag('div', get_string('level', 'local_student_monitor'), ['class' => 'text-muted']);
    echo html_writer::end_div();

    // Streak.
    echo html_writer::start_div('col-md-3 text-center');
    echo html_writer::tag('div', $userstats->current_streak, ['class' => 'display-4 text-warning']);
    echo html_writer::tag('div', get_string('currentstreak', 'local_student_monitor'), ['class' => 'text-muted']);
    echo html_writer::end_div();

    // Achievements.
    echo html_writer::start_div('col-md-3 text-center');
    $achievementcount = $DB->count_records('local_sm_achievements', ['userid' => $USER->id]);
    echo html_writer::tag('div', $achievementcount, ['class' => 'display-4 text-info']);
    echo html_writer::tag('div', get_string('achievements', 'local_student_monitor'), ['class' => 'text-muted']);
    echo html_writer::end_div();

    echo html_writer::end_div(); // Row.

    // Progress to next level.
    $nextlevelpoints = 100 * pow(1.2, $userstats->level);
    $progress = ($userstats->total_points / $nextlevelpoints) * 100;
    $progress = min($progress, 100);

    echo html_writer::start_div('mt-3');
    echo html_writer::tag('small', get_string('progresstonextlevel', 'local_student_monitor'));
    echo html_writer::start_div('progress', ['style' => 'height: 25px;']);
    echo html_writer::div(
        round($progress) . '%',
        'progress-bar bg-success',
        ['style' => 'width: ' . $progress . '%', 'role' => 'progressbar']
    );
    echo html_writer::end_div();
    echo html_writer::tag('small', get_string('pointstonextlevel', 'local_student_monitor', [
        'current' => $userstats->total_points,
        'needed' => round($nextlevelpoints)
    ]), ['class' => 'text-muted']);
    echo html_writer::end_div();

    echo html_writer::end_div();
    echo html_writer::end_div();
}

// Leaderboard table.
echo html_writer::tag('h3', get_string('topleaders', 'local_student_monitor'), ['class' => 'mt-4']);

if (empty($leaderboard)) {
    echo html_writer::div(
        get_string('noleaderboarddata', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('rank', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('student', 'local_student_monitor'));
    echo html_writer::tag('th', get_string('points', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('level', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('streak', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::tag('th', get_string('achievements', 'local_student_monitor'), ['class' => 'text-center']);
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    $rank = 1;
    foreach ($leaderboard as $entry) {
        $iscurrentuser = $entry->userid == $USER->id;
        $rowclass = $iscurrentuser ? 'table-primary' : '';

        echo html_writer::start_tag('tr', ['class' => $rowclass]);

        // Rank with medal for top 3.
        echo html_writer::start_tag('td', ['class' => 'text-center']);
        if ($rank == 1) {
            echo html_writer::tag('span', '🥇', ['style' => 'font-size: 1.5em;']) . ' ' . $rank;
        } else if ($rank == 2) {
            echo html_writer::tag('span', '🥈', ['style' => 'font-size: 1.5em;']) . ' ' . $rank;
        } else if ($rank == 3) {
            echo html_writer::tag('span', '🥉', ['style' => 'font-size: 1.5em;']) . ' ' . $rank;
        } else {
            echo $rank;
        }
        echo html_writer::end_tag('td');

        // Student name.
        echo html_writer::start_tag('td');
        $user = $DB->get_record('user', ['id' => $entry->userid]);
        if ($user) {
            $userurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
            echo html_writer::link($userurl, fullname($user));
            if ($iscurrentuser) {
                echo ' ' . html_writer::tag('span', get_string('you', 'local_student_monitor'),
                    ['class' => 'badge badge-info']);
            }
        }
        echo html_writer::end_tag('td');

        // Points.
        echo html_writer::tag('td', html_writer::tag('strong', $entry->total_points),
            ['class' => 'text-center']);

        // Level.
        echo html_writer::tag('td', html_writer::tag('span', get_string('leveln', 'local_student_monitor',
            ['level' => $entry->level]), ['class' => 'badge badge-success']), ['class' => 'text-center']);

        // Streak.
        $streakicon = $entry->current_streak >= 7 ? '🔥' : '📅';
        echo html_writer::tag('td', $streakicon . ' ' . $entry->current_streak . ' ' .
            get_string('days', 'local_student_monitor'), ['class' => 'text-center']);

        // Achievements.
        $achievementcount = $DB->count_records('local_sm_achievements', ['userid' => $entry->userid]);
        echo html_writer::tag('td', html_writer::tag('span', $achievementcount,
            ['class' => 'badge badge-primary']), ['class' => 'text-center']);

        echo html_writer::end_tag('tr');

        $rank++;
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
}

// Recent achievements.
echo html_writer::tag('h3', get_string('recentachievements', 'local_student_monitor'), ['class' => 'mt-4']);

$recentachievements = $DB->get_records_sql("
    SELECT a.id,
           a.userid,
           a.achievement_key,
           a.points_awarded,
           a.timecreated,
           u.firstname,
           u.lastname
    FROM {local_sm_achievements} a
    JOIN {user} u ON u.id = a.userid
    ORDER BY a.timecreated DESC
    LIMIT 20
");

if (empty($recentachievements)) {
    echo html_writer::div(
        get_string('noachievements', 'local_student_monitor'),
        'alert alert-info'
    );
} else {
    echo html_writer::start_div('list-group');

    foreach ($recentachievements as $achievement) {
        $achievementdata = \local_student_monitor\manager\gamification_manager::ACHIEVEMENTS[$achievement->achievement_key] ?? null;

        if (!$achievementdata) {
            continue;
        }

        echo html_writer::start_div('list-group-item');

        echo html_writer::start_div('d-flex w-100 justify-content-between');
        echo html_writer::tag('h5', $achievementdata['badge'] . ' ' .
            get_string('achievement_' . $achievement->achievement_key, 'local_student_monitor'),
            ['class' => 'mb-1']);
        echo html_writer::tag('small', userdate($achievement->timecreated, get_string('strftimedatetimeshort')));
        echo html_writer::end_div();

        echo html_writer::tag('p', fullname($achievement) . ' ' .
            get_string('earned', 'local_student_monitor') . ' ' .
            $achievement->points_awarded . ' ' . get_string('points', 'local_student_monitor'),
            ['class' => 'mb-1']);

        echo html_writer::end_div();
    }

    echo html_writer::end_div();
}

// Back to dashboard.
if (has_capability('local/student_monitor:managesettings', context_system::instance())) {
    $backurl = new moodle_url('/local/student_monitor/dashboard.php');
    echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
        ['class' => 'btn btn-secondary mt-4']);
}

echo $OUTPUT->footer();
