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
 * Email template editor page.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/student_monitor:managetemplates', context_system::instance());

$templateid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'list', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/student_monitor/template_editor.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('templateeditor', 'local_student_monitor'));
$PAGE->set_heading(get_string('templateeditor', 'local_student_monitor'));
$PAGE->set_pagelayout('admin');

// Handle form submission.
if ($action === 'save' && confirm_sesskey()) {
    $templatedata = new stdClass();
    $templatedata->id = required_param('templateid', PARAM_INT);
    $templatedata->subject = required_param('subject', PARAM_TEXT);
    $templatedata->body = required_param('body', PARAM_RAW);
    $templatedata->timemodified = time();

    $DB->update_record('local_sm_templates', $templatedata);

    redirect(
        new moodle_url('/local/student_monitor/template_editor.php'),
        get_string('templatesaved', 'local_student_monitor'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Handle delete action.
if ($action === 'delete' && $templateid && confirm_sesskey()) {
    $DB->delete_records('local_sm_templates', ['id' => $templateid]);

    redirect(
        new moodle_url('/local/student_monitor/template_editor.php'),
        get_string('templatedeleted', 'local_student_monitor'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Handle reset to default action.
if ($action === 'reset' && $templateid && confirm_sesskey()) {
    // Get default template from install.php logic.
    $template = $DB->get_record('local_sm_templates', ['id' => $templateid], '*', MUST_EXIST);

    // Reset to default French templates (you can enhance this to support other languages).
    $defaulttemplates = [
        'inactivity_level1' => [
            'subject' => 'Nous n\'avons pas de nouvelles de vous depuis {days} jours',
            'body' => 'Bonjour {firstname},

Nous avons remarqué que vous n\'avez pas accédé à votre espace UNCHK depuis {days} jours.

Votre dernier accès remonte au {lastaccess}.

Nous vous encourageons à vous reconnecter pour ne rien manquer de vos cours et activités pédagogiques.

Si vous rencontrez des difficultés, n\'hésitez pas à nous contacter : {supportemail}

Cordialement,
L\'équipe UNCHK'
        ],
        'inactivity_level2' => [
            'subject' => '⚠️ Votre absence nous inquiète',
            'body' => 'Bonjour {firstname},

Nous constatons que vous n\'avez pas accédé à vos cours depuis {days} jours.

📊 Votre niveau de risque actuel: {riskLevel}

Cette absence prolongée peut compromettre votre réussite académique. Nous vous encourageons vivement à reprendre vos activités pédagogiques dès que possible.

Si vous rencontrez des difficultés (techniques, personnelles, pédagogiques), notre équipe est là pour vous aider : {supportemail} - {supportphone}

Cordialement,
L\'équipe UNCHK'
        ],
        'inactivity_level3' => [
            'subject' => 'URGENT - Absence critique sur Moodle',
            'body' => 'Bonjour {firstname},

Votre absence sur la plateforme Moodle est préoccupante ({days} jours).

Votre niveau de risque est maintenant : {riskLevel}

Un conseiller pédagogique va prendre contact avec vous. Merci de vous reconnecter et de consulter vos cours dès que possible.

Support urgent : {supportemail} - {supportphone}

L\'équipe UNCHK'
        ],
        'new_content' => [
            'subject' => '📚 Nouveau contenu disponible: {modulename}',
            'body' => 'Bonjour {firstname},

Un nouveau contenu pédagogique vient d\'être ajouté dans votre cours "{coursename}":

📖 {modulename}

🔗 Accéder au contenu: {modulelink}

Bon apprentissage!
L\'équipe pédagogique'
        ],
        'assignment_reminder_7days' => [
            'subject' => '📝 Rappel - Devoir à rendre dans 7 jours',
            'body' => 'Bonjour {firstname},

Rappel: le devoir "{assignmentname}" est à rendre dans 7 jours.

📅 Date limite: {duedate}
📚 Cours: {coursename}
🔗 Déposer votre travail: {submissionlink}

Prenez de l\'avance pour ne pas être pris au dépourvu!

Bon courage,
L\'équipe pédagogique'
        ],
        'assignment_reminder_1day' => [
            'subject' => '⚠️ URGENT - Devoir à rendre DEMAIN!',
            'body' => 'Bonjour {firstname},

Le devoir "{assignmentname}" est à rendre DEMAIN!

📅 Date limite: {duedate}
🔗 Déposer MAINTENANT: {submissionlink}

Ne laissez pas passer la deadline!

Bon courage,
L\'équipe pédagogique'
        ],
        'institutional_announcement' => [
            'subject' => '📢 Annonce UNCHK: {title}',
            'body' => 'Bonjour {firstname},

{message}

Pour plus d\'informations, consultez le forum institutionnel.

L\'administration UNCHK'
        ]
    ];

    if (isset($defaulttemplates[$template->type])) {
        $default = $defaulttemplates[$template->type];
        $template->subject = $default['subject'];
        $template->body = $default['body'];
        $template->timemodified = time();

        $DB->update_record('local_sm_templates', $template);

        redirect(
            new moodle_url('/local/student_monitor/template_editor.php'),
            get_string('templateresettodefault', 'local_student_monitor'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

echo $OUTPUT->header();

echo html_writer::tag('h2', '✉️ ' . get_string('templateeditor', 'local_student_monitor'), ['class' => 'sm-page-title']);

if ($action === 'edit' && $templateid) {
    // Edit template form.
    $template = $DB->get_record('local_sm_templates', ['id' => $templateid], '*', MUST_EXIST);

    echo html_writer::tag('h3', get_string('edittemplate', 'local_student_monitor') . ': ' .
        get_string($template->type, 'local_student_monitor'));

    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => new moodle_url('/local/student_monitor/template_editor.php'),
        'class' => 'mt-3'
    ]);

    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'templateid', 'value' => $template->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    // Subject field.
    echo html_writer::start_div('form-group');
    echo html_writer::tag('label', get_string('subject', 'local_student_monitor'), ['for' => 'subject']);
    echo html_writer::empty_tag('input', [
        'type' => 'text',
        'name' => 'subject',
        'id' => 'subject',
        'class' => 'form-control',
        'value' => $template->subject,
        'required' => 'required'
    ]);
    echo html_writer::end_div();

    // Body field.
    echo html_writer::start_div('form-group');
    echo html_writer::tag('label', get_string('body', 'local_student_monitor'), ['for' => 'body']);
    echo html_writer::tag('textarea', s($template->body), [
        'name' => 'body',
        'id' => 'body',
        'class' => 'form-control',
        'rows' => '12',
        'required' => 'required'
    ]);
    echo html_writer::end_div();

    // Available placeholders.
    echo html_writer::start_div('alert alert-info');
    echo html_writer::tag('strong', get_string('availableplaceholders', 'local_student_monitor'));
    echo html_writer::tag('p', get_string('placeholdersdesc', 'local_student_monitor'));
    echo html_writer::start_tag('ul');
    $placeholders = [
        '{firstname}', '{lastname}', '{fullname}', '{email}',
        '{currentdate}', '{institutionname}',
        '{supportemail}', '{supportphone}'
    ];

    // Template-specific placeholders.
    if (strpos($template->type, 'inactivity') !== false) {
        $placeholders = array_merge($placeholders, ['{days}', '{lastaccess}', '{riskLevel}']);
    } else if (strpos($template->type, 'assignment_reminder') !== false) {
        $placeholders = array_merge($placeholders, ['{assignmentname}', '{duedate}', '{submissionlink}', '{coursename}']);
    } else if ($template->type === 'new_content') {
        $placeholders = array_merge($placeholders, ['{coursename}', '{modulename}', '{modulelink}']);
    } else if ($template->type === 'institutional_announcement') {
        $placeholders = array_merge($placeholders, ['{title}', '{message}']);
    }

    foreach ($placeholders as $placeholder) {
        echo html_writer::tag('li', html_writer::tag('code', $placeholder));
    }
    echo html_writer::end_tag('ul');
    echo html_writer::end_div();

    // Buttons.
    echo html_writer::start_div('form-group');
    echo html_writer::tag('button', get_string('savechanges'), [
        'type' => 'submit',
        'class' => 'btn btn-primary'
    ]);

    $reseturl = new moodle_url('/local/student_monitor/template_editor.php', [
        'action' => 'reset',
        'id' => $template->id,
        'sesskey' => sesskey()
    ]);
    echo html_writer::link($reseturl, get_string('resettodefault', 'local_student_monitor'),
        ['class' => 'btn btn-warning ml-2']);

    $cancelurl = new moodle_url('/local/student_monitor/template_editor.php');
    echo html_writer::link($cancelurl, get_string('cancel'), ['class' => 'btn btn-secondary ml-2']);
    echo html_writer::end_div();

    echo html_writer::end_tag('form');

} else {
    // List all templates.
    echo html_writer::tag('p', get_string('templateeditordesc', 'local_student_monitor'), ['class' => 'alert alert-info']);

    $templates = $DB->get_records('local_sm_templates', ['language' => 'fr'], 'type');

    if (empty($templates)) {
        echo html_writer::tag('p', get_string('notemplates', 'local_student_monitor'), ['class' => 'text-muted']);
    } else {
        echo html_writer::start_tag('table', ['class' => 'table table-striped']);
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', get_string('templatetype', 'local_student_monitor'));
        echo html_writer::tag('th', get_string('subject', 'local_student_monitor'));
        echo html_writer::tag('th', get_string('lastmodified', 'local_student_monitor'));
        echo html_writer::tag('th', get_string('actions', 'local_student_monitor'));
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($templates as $template) {
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', get_string($template->type, 'local_student_monitor'));
            echo html_writer::tag('td', s($template->subject));
            echo html_writer::tag('td', userdate($template->timemodified));

            echo html_writer::start_tag('td');
            $editurl = new moodle_url('/local/student_monitor/template_editor.php', [
                'action' => 'edit',
                'id' => $template->id
            ]);
            echo html_writer::link($editurl, get_string('edit'), ['class' => 'btn btn-sm btn-primary']);
            echo html_writer::end_tag('td');

            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
    }
}

// Back button.
$backurl = new moodle_url('/local/student_monitor/dashboard.php');
echo html_writer::link($backurl, get_string('backtodashboard', 'local_student_monitor'),
    ['class' => 'btn btn-secondary mt-3']);

echo $OUTPUT->footer();
