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
 * Weekly Report page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/student_monitor:viewreports', $context);

$PAGE->set_url(new moodle_url('/local/student_monitor/weekly_report.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('weeklyreport', 'local_student_monitor'));
$PAGE->set_heading(get_string('weeklyreport', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Add CSS.
$PAGE->requires->css('/local/student_monitor/styles/styles.css');

// Initialize tracker.
$tracker = new \local_student_monitor\manager\student_tracker();

// Get statistics.
$stats = $tracker->get_statistics();

// Get notification stats for the week.
$weekago = time() - (7 * 86400);

$sql = "SELECT type, COUNT(*) as count
          FROM {local_sm_notifications}
         WHERE timecreated >= :weekago
         GROUP BY type";

$notifstats = $DB->get_records_sql($sql, ['weekago' => $weekago]);

// Calculate totals.
$totalnotifications = 0;
$automaticalerts = 0;
$manualalerts = 0;

foreach ($notifstats as $stat) {
    $totalnotifications += $stat->count;

    // Count automatic alerts (inactivity levels and risk-based alerts).
    if (strpos($stat->type, 'inactivity_level') !== false ||
        strpos($stat->type, 'risk_') !== false ||
        $stat->type === 'assignment_reminder') {
        $automaticalerts += $stat->count;
    } else if ($stat->type === 'manual_alert') {
        $manualalerts += $stat->count;
    }
}

// Get automatic alerts details.
$autostats = [];
if ($automaticalerts > 0) {
    $autosql = "SELECT type, COUNT(*) as count,
                       SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                       SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as readcount,
                       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                  FROM {local_sm_notifications}
                 WHERE timecreated >= :weekago
                   AND (type LIKE 'inactivity_level%'
                    OR type LIKE 'risk_%'
                    OR type = 'assignment_reminder')
                 GROUP BY type";

    $autostats = $DB->get_records_sql($autosql, ['weekago' => $weekago]);
}

// Output starts here.
echo $OUTPUT->header();

// Page title with icon.
echo html_writer::tag('h2', '📊 ' . get_string('weeklyreport', 'local_student_monitor'));

// Overview cards.
echo html_writer::start_div('row mb-4');

// Total students.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-primary text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('totalstudents', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $stats->total_students, ['class' => 'display-4']);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Total notifications.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-info text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', get_string('totalnotifications', 'local_student_monitor'), ['class' => 'card-title']);
echo html_writer::tag('div', $totalnotifications, ['class' => 'display-4']);
echo html_writer::tag('small', get_string('last30days', 'local_student_monitor'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Automatic alerts.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-success text-white');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', 'Alertes automatiques', ['class' => 'card-title']);
echo html_writer::tag('div', $automaticalerts, ['class' => 'display-4']);
echo html_writer::tag('small', 'Cette semaine');
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

// Manual alerts.
echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card bg-warning text-dark');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag('h6', 'Alertes manuelles', ['class' => 'card-title']);
echo html_writer::tag('div', $manualalerts, ['class' => 'display-4']);
echo html_writer::tag('small', 'Cette semaine');
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // row.

// Risk Level Distribution.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', 'Distribution des niveaux de risque', ['class' => 'card-title']);

echo html_writer::start_div('row text-center mt-4');

// CRITIQUE.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('h5', get_string('risk_critique', 'local_student_monitor'), ['class' => 'text-danger']);
echo html_writer::tag('div', $stats->critique, ['class' => 'display-3 text-danger']);
echo html_writer::tag('small', get_string('students', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();

// ÉLEVÉ.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('h5', get_string('risk_eleve', 'local_student_monitor'), ['class' => 'text-warning']);
echo html_writer::tag('div', $stats->eleve, ['class' => 'display-3 text-warning']);
echo html_writer::tag('small', get_string('students', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();

// MOYEN.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('h5', get_string('risk_moyen', 'local_student_monitor'), ['class' => 'text-info']);
echo html_writer::tag('div', $stats->moyen, ['class' => 'display-3 text-info']);
echo html_writer::tag('small', get_string('students', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();

// FAIBLE.
echo html_writer::start_div('col-md-3');
echo html_writer::tag('h5', get_string('risk_faible', 'local_student_monitor'), ['class' => 'text-success']);
echo html_writer::tag('div', $stats->faible, ['class' => 'display-3 text-success']);
echo html_writer::tag('small', get_string('students', 'local_student_monitor'), ['class' => 'text-muted']);
echo html_writer::end_div();

echo html_writer::end_div(); // row.
echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Automatic alerts breakdown.
if (!empty($autostats)) {
    echo html_writer::start_div('card mb-4');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h4', 'Détail des alertes automatiques', ['class' => 'card-title']);

    $table = new html_table();
    $table->attributes['class'] = 'table table-striped';
    $table->head = [
        'Type d\'alerte',
        'Total',
        'Envoyées',
        'Lues',
        'Taux de lecture',
        'Échecs',
    ];

    foreach ($autostats as $autostat) {
        $readrate = $autostat->sent > 0 ? round(($autostat->readcount / $autostat->sent) * 100, 1) : 0;

        // Format type name.
        $typename = $autostat->type;
        if (strpos($typename, 'inactivity_level') === 0) {
            $typename = str_replace('inactivity_level', 'Inactivité niveau ', $typename);
        } else if (strpos($typename, 'risk_') === 0) {
            $typename = str_replace('risk_', 'Risque ', $typename);
            $typename = str_replace(['critique', 'eleve', 'moyen', 'faible'],
                                   ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'],
                                   $typename);
        } else if ($typename === 'assignment_reminder') {
            $typename = 'Rappel de devoir';
        }

        $row = [
            $typename,
            $autostat->count,
            $autostat->sent,
            $autostat->readcount,
            html_writer::tag('span', $readrate . '%',
                ['class' => $readrate > 50 ? 'badge badge-success' : 'badge badge-warning']),
            $autostat->failed > 0 ?
                html_writer::tag('span', $autostat->failed, ['class' => 'text-danger font-weight-bold']) :
                $autostat->failed,
        ];

        $table->data[] = $row;
    }

    echo html_writer::table($table);

    echo html_writer::end_div(); // card-body.
    echo html_writer::end_div(); // card.
}

// Additional statistics.
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h4', 'Statistiques supplémentaires', ['class' => 'card-title']);

echo html_writer::start_div('row');

echo html_writer::start_div('col-md-6');
echo html_writer::tag('h6', 'Interventions nécessaires', ['class' => 'text-muted']);
echo html_writer::tag('div', $stats->intervention_needed . ' ' . get_string('students', 'local_student_monitor'),
    ['class' => 'h4']);
echo html_writer::end_div();

echo html_writer::start_div('col-md-6');
echo html_writer::tag('h6', 'Inactivité moyenne', ['class' => 'text-muted']);
echo html_writer::tag('div', round($stats->avg_inactivity, 1) . ' jours', ['class' => 'h4']);
echo html_writer::end_div();

echo html_writer::end_div(); // row.
echo html_writer::end_div(); // card-body.
echo html_writer::end_div(); // card.

// Back button.
$dashboardurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($dashboardurl, '← ' . get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary']);

echo $OUTPUT->footer();
