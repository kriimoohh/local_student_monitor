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
 * Post-installation tasks for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 *
 * @return bool
 */
function xmldb_local_student_monitor_install() {
    global $DB;

    $now = time();

    // Create default templates for French.
    $templates = [
        // Inactivity Level 1 (72 hours / 3 days).
        [
            'name' => 'Inactivité Niveau 1',
            'type' => 'inactivity_level1',
            'subject' => 'Nous n\'avons pas de nouvelles de vous depuis {days} jours',
            'body' => 'Bonjour {firstname},

Nous avons remarqué que vous n\'avez pas accédé à votre espace UNCHK depuis {days} jours.

Votre dernier accès remonte au {lastaccess}.

Nous vous encourageons à vous reconnecter pour ne rien manquer de vos cours et activités pédagogiques.

Si vous rencontrez des difficultés, n\'hésitez pas à nous contacter : {supportemail}

Cordialement,
L\'équipe UNCHK',
            'placeholders' => json_encode(['firstname', 'lastname', 'days', 'lastaccess', 'supportemail']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // Inactivity Level 2 (7 days).
        [
            'name' => 'Inactivité Niveau 2',
            'type' => 'inactivity_level2',
            'subject' => '⚠️ Votre absence nous inquiète',
            'body' => 'Bonjour {firstname},

Nous constatons que vous n\'avez pas accédé à vos cours depuis {days} jours.

📊 Votre niveau de risque actuel: {riskLevel}

Cette absence prolongée peut compromettre votre réussite académique. Nous vous encourageons vivement à reprendre vos activités pédagogiques dès que possible.

Si vous rencontrez des difficultés (techniques, personnelles, pédagogiques), notre équipe est là pour vous aider.

📧 Email: {supportemail}
📞 Téléphone: {supportphone}

Nous comptons sur vous,
L\'équipe UNCHK',
            'placeholders' => json_encode(['firstname', 'lastname', 'days', 'lastaccess', 'riskLevel', 'supportemail', 'supportphone']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // Inactivity Level 3 (14 days).
        [
            'name' => 'Inactivité Niveau 3',
            'type' => 'inactivity_level3',
            'subject' => '🚨 URGENT - Absence prolongée détectée',
            'body' => 'Bonjour {firstname},

Nous sommes très préoccupés par votre absence prolongée de {days} jours.

🚨 Votre niveau de risque: {riskLevel}

Vous êtes en train de prendre du retard important dans vos études. Il est URGENT de reprendre contact avec nous.

Un conseiller pédagogique va vous contacter dans les prochaines 48 heures pour vous accompagner.

CONTACTEZ-NOUS IMMÉDIATEMENT:
📧 Email: {supportemail}
📞 Téléphone: {supportphone}

Nous sommes là pour vous aider,
L\'équipe UNCHK',
            'placeholders' => json_encode(['firstname', 'lastname', 'days', 'lastaccess', 'riskLevel', 'supportemail', 'supportphone']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // New content notification.
        [
            'name' => 'Nouvelle séquence pédagogique',
            'type' => 'new_content',
            'subject' => '📚 Nouveau contenu disponible: {modulename}',
            'body' => 'Bonjour {firstname},

Un nouveau contenu pédagogique vient d\'être ajouté dans votre cours "{coursename}":

📖 {modulename}

🔗 Accéder au contenu: {modulelink}

Bon apprentissage!
L\'équipe pédagogique',
            'placeholders' => json_encode(['firstname', 'lastname', 'coursename', 'modulename', 'modulelink']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // Assignment reminder J-7.
        [
            'name' => 'Rappel devoir J-7',
            'type' => 'assignment_reminder_7days',
            'subject' => '📝 Rappel - Devoir à rendre dans 7 jours',
            'body' => 'Bonjour {firstname},

Rappel: le devoir "{assignmentname}" est à rendre dans 7 jours.

📅 Date limite: {duedate}
📚 Cours: {coursename}
🔗 Déposer votre travail: {submissionlink}

Prenez de l\'avance pour ne pas être pris au dépourvu!

Bon courage,
L\'équipe pédagogique',
            'placeholders' => json_encode(['firstname', 'lastname', 'assignmentname', 'duedate', 'coursename', 'submissionlink']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // Assignment reminder J-1.
        [
            'name' => 'Rappel devoir J-1',
            'type' => 'assignment_reminder_1day',
            'subject' => '⚠️ URGENT - Devoir à rendre DEMAIN!',
            'body' => 'Bonjour {firstname},

Le devoir "{assignmentname}" est à rendre DEMAIN!

📅 Date limite: {duedate}
🔗 Déposer MAINTENANT: {submissionlink}

Ne laissez pas passer la deadline!

Bon courage,
L\'équipe pédagogique',
            'placeholders' => json_encode(['firstname', 'lastname', 'assignmentname', 'duedate', 'submissionlink']),
            'is_default' => 1,
            'language' => 'fr',
        ],

        // Institutional announcement.
        [
            'name' => 'Annonce institutionnelle',
            'type' => 'institutional_announcement',
            'subject' => '📢 Annonce UNCHK: {title}',
            'body' => 'Bonjour {firstname},

{message}

Pour plus d\'informations, consultez le forum institutionnel.

L\'administration UNCHK',
            'placeholders' => json_encode(['firstname', 'lastname', 'title', 'message']),
            'is_default' => 1,
            'language' => 'fr',
        ],
    ];

    // Insert templates.
    foreach ($templates as $template) {
        $template['timecreated'] = $now;
        $template['timemodified'] = $now;
        $DB->insert_record('local_sm_templates', (object) $template);
    }

    return true;
}
