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
 * French language strings for Student Monitor plugin.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Student Monitor';

// Capabilities.
$string['student_monitor:viewdashboard'] = 'Voir le tableau de bord Student Monitor';
$string['student_monitor:managesettings'] = 'Gérer les paramètres Student Monitor';
$string['student_monitor:sendmanual'] = 'Envoyer des alertes manuelles';
$string['student_monitor:viewreports'] = 'Voir les rapports et statistiques';
$string['student_monitor:viewstudentdata'] = 'Voir les données des étudiants';
$string['student_monitor:intervene'] = 'Intervenir auprès des étudiants';
$string['student_monitor:exportdata'] = 'Exporter les données';
$string['student_monitor:managetemplates'] = 'Gérer les modèles de messages';

// General settings.
$string['generalsettings'] = 'Paramètres généraux';
$string['generalsettingsdesc'] = 'Configuration générale du plugin Student Monitor';
$string['enabled'] = 'Activer Student Monitor';
$string['enabled_desc'] = 'Activer ou désactiver le plugin Student Monitor';

// Inactivity settings.
$string['inactivitysettings'] = 'Paramètres de détection d\'inactivité';
$string['inactivitysettingsdesc'] = 'Configuration des seuils de détection d\'inactivité';
$string['inactivitythreshold1'] = 'Seuil niveau 1 (jours)';
$string['inactivitythreshold1_desc'] = 'Nombre de jours d\'inactivité pour déclencher une alerte de niveau 1 (défaut: 3 jours)';
$string['inactivitythreshold2'] = 'Seuil niveau 2 (jours)';
$string['inactivitythreshold2_desc'] = 'Nombre de jours d\'inactivité pour déclencher une alerte de niveau 2 (défaut: 7 jours)';
$string['inactivitythreshold3'] = 'Seuil niveau 3 (jours)';
$string['inactivitythreshold3_desc'] = 'Nombre de jours d\'inactivité pour déclencher une alerte de niveau 3 (défaut: 14 jours)';

// Assignment reminder settings.
$string['assignmentremindersettings'] = 'Paramètres des rappels de devoirs';
$string['assignmentremindersettingsdesc'] = 'Configuration des rappels automatiques pour les devoirs';
$string['assignmentreminders'] = 'Activer les rappels de devoirs';
$string['assignmentreminders_desc'] = 'Envoyer des rappels automatiques avant les échéances de devoirs';
$string['reminderdays'] = 'Jours de rappel';
$string['reminderdays_desc'] = 'Liste des jours avant échéance pour envoyer des rappels (séparés par des virgules, ex: 7,3,1)';

// Institutional announcement settings.
$string['institutionalannouncementsettings'] = 'Paramètres des annonces institutionnelles';
$string['institutionalannouncementsettingsdesc'] = 'Configuration pour les annonces institutionnelles';
$string['institutionalforumid'] = 'ID du forum institutionnel';
$string['institutionalforumid_desc'] = 'ID du forum Moodle utilisé pour les annonces institutionnelles';

// Channel settings.
$string['channelsettings'] = 'Canaux de notification';
$string['channelsettingsdesc'] = 'Configuration des différents canaux de notification';
$string['channelemail'] = 'Activer les emails';
$string['channelemail_desc'] = 'Envoyer les notifications par email';
$string['channelmoodle'] = 'Activer les notifications Moodle';
$string['channelmoodle_desc'] = 'Envoyer les notifications via le système de notification Moodle';
$string['channelsms'] = 'Activer les SMS';
$string['channelsms_desc'] = 'Envoyer les notifications par SMS (nécessite configuration API)';
$string['smsapiurl'] = 'URL de l\'API SMS';
$string['smsapiurl_desc'] = 'URL de l\'API pour envoyer des SMS';
$string['smsapikey'] = 'Clé API SMS';
$string['smsapikey_desc'] = 'Clé d\'authentification pour l\'API SMS';
$string['channelwhatsapp'] = 'Activer WhatsApp';
$string['channelwhatsapp_desc'] = 'Envoyer les notifications via WhatsApp Business API';
$string['whatsappphoneid'] = 'ID du numéro WhatsApp';
$string['whatsappphoneid_desc'] = 'ID du numéro de téléphone WhatsApp Business';
$string['whatsapptoken'] = 'Token d\'accès WhatsApp';
$string['whatsapptoken_desc'] = 'Token d\'accès pour l\'API WhatsApp Business';

// Support settings.
$string['supportsettings'] = 'Contact support';
$string['supportsettingsdesc'] = 'Informations de contact du support';
$string['supportemail'] = 'Email du support';
$string['supportemail_desc'] = 'Adresse email pour le support étudiant';
$string['supportphone'] = 'Téléphone du support';
$string['supportphone_desc'] = 'Numéro de téléphone pour le support étudiant';

// Dashboard.
$string['dashboard'] = 'Tableau de bord';
$string['studentmonitordashboard'] = 'Tableau de bord Student Monitor';
$string['studentmonitorsettings'] = 'Paramètres Student Monitor';
$string['statistics'] = 'Statistiques';
$string['studentsatrisk'] = 'Étudiants à risque';
$string['notificationssent'] = 'Notifications envoyées';
$string['interventionsneeded'] = 'Interventions nécessaires';
$string['readrate'] = 'Taux de lecture';
$string['criticalalerts'] = 'Alertes critiques';
$string['studentlist'] = 'Liste des étudiants';
$string['quickactions'] = 'Actions rapides';

// Risk levels.
$string['risklevel'] = 'Niveau de risque';
$string['risk_faible'] = 'FAIBLE';
$string['risk_moyen'] = 'MOYEN';
$string['risk_eleve'] = 'ÉLEVÉ';
$string['risk_critique'] = 'CRITIQUE';

// Notification types.
$string['notificationtype'] = 'Type de notification';
$string['inactivitylevel1'] = 'Inactivité niveau 1';
$string['inactivitylevel2'] = 'Inactivité niveau 2';
$string['inactivitylevel3'] = 'Inactivité niveau 3';
$string['newcontent'] = 'Nouveau contenu';
$string['assignmentreminder'] = 'Rappel de devoir';
$string['institutionalannouncement'] = 'Annonce institutionnelle';
$string['manualalert'] = 'Alerte manuelle';

// Notification status.
$string['status'] = 'Statut';
$string['status_pending'] = 'En attente';
$string['status_sent'] = 'Envoyé';
$string['status_delivered'] = 'Délivré';
$string['status_read'] = 'Lu';
$string['status_failed'] = 'Échec';

// Manual alerts.
$string['createalert'] = 'Créer une alerte';
$string['alerttype'] = 'Type d\'alerte';
$string['alert_exam'] = 'Examen';
$string['alert_assignment'] = 'Devoir';
$string['alert_announcement'] = 'Annonce';
$string['alert_event'] = 'Événement';
$string['title'] = 'Titre';
$string['eventdate'] = 'Date de l\'événement';
$string['description'] = 'Description';
$string['channels'] = 'Canaux';
$string['recipients'] = 'Destinataires';
$string['recipients_all_course'] = 'Tout le cours';
$string['recipients_group'] = 'Groupe spécifique';
$string['recipients_manual'] = 'Sélection manuelle';
$string['reminder7days'] = 'Rappel J-7';
$string['reminder3days'] = 'Rappel J-3';
$string['reminder1day'] = 'Rappel J-1';
$string['sendalert'] = 'Envoyer l\'alerte';

// Student tracking.
$string['studentname'] = 'Nom de l\'étudiant';
$string['lastactivity'] = 'Dernière activité';
$string['inactivitydays'] = 'Jours d\'inactivité';
$string['missingassignments'] = 'Devoirs manquants';
$string['notificationcount'] = 'Nombre de notifications';
$string['interventionneeded'] = 'Intervention nécessaire';
$string['assignedto'] = 'Assigné à';
$string['notes'] = 'Notes';
$string['actions'] = 'Actions';

// Reports.
$string['weeklyreport'] = 'Rapport hebdomadaire';
$string['exportcsv'] = 'Exporter CSV';
$string['exportpdf'] = 'Exporter PDF';

// Tasks.
$string['task_check_inactivity'] = 'Vérifier l\'inactivité des étudiants';
$string['task_check_assignments_due'] = 'Vérifier les devoirs à échéance';
$string['task_send_scheduled_notifications'] = 'Envoyer les notifications programmées';
$string['task_update_student_tracking'] = 'Mettre à jour le suivi des étudiants';
$string['task_generate_weekly_report'] = 'Générer le rapport hebdomadaire';
$string['task_cleanup_old_logs'] = 'Nettoyer les anciens logs';

// Messages.
$string['alertcreated'] = 'Alerte créée avec succès';
$string['alertsent'] = 'Alerte envoyée avec succès';
$string['notificationsent'] = 'Notification envoyée';
$string['dataexported'] = 'Données exportées avec succès';
$string['settingssaved'] = 'Paramètres enregistrés';

// Errors.
$string['error_sending_notification'] = 'Erreur lors de l\'envoi de la notification';
$string['error_creating_alert'] = 'Erreur lors de la création de l\'alerte';
$string['error_exporting_data'] = 'Erreur lors de l\'export des données';
$string['nopermission'] = 'Vous n\'avez pas la permission d\'accéder à cette page';

// Privacy.
$string['privacy:metadata:local_sm_notifications'] = 'Informations sur les notifications envoyées aux utilisateurs';
$string['privacy:metadata:local_sm_notifications:userid'] = 'ID de l\'utilisateur destinataire';
$string['privacy:metadata:local_sm_notifications:message'] = 'Contenu de la notification';
$string['privacy:metadata:local_sm_notifications:timecreated'] = 'Date de création de la notification';
$string['privacy:metadata:local_sm_notifications:timeread'] = 'Date de lecture de la notification';

$string['privacy:metadata:local_sm_student_tracking'] = 'Données de suivi des étudiants';
$string['privacy:metadata:local_sm_student_tracking:userid'] = 'ID de l\'étudiant';
$string['privacy:metadata:local_sm_student_tracking:risk_level'] = 'Niveau de risque calculé';
$string['privacy:metadata:local_sm_student_tracking:last_activity'] = 'Date de dernière activité';
$string['privacy:metadata:local_sm_student_tracking:notes'] = 'Notes du superviseur';

$string['privacy:metadata:local_sm_logs'] = 'Logs des actions effectuées';
$string['privacy:metadata:local_sm_logs:userid'] = 'ID de l\'utilisateur';
$string['privacy:metadata:local_sm_logs:action'] = 'Action effectuée';
$string['privacy:metadata:local_sm_logs:details'] = 'Détails de l\'action';

// Events.
$string['event_notification_sent'] = 'Notification envoyée';
$string['event_alert_created'] = 'Alerte créée';

// Privacy export data.
$string['privacy:notifications'] = 'Notifications';
$string['privacy:tracking'] = 'Suivi étudiant';
$string['privacy:logs'] = 'Journaux d\'activité';

// Additional strings.
$string['nostudents'] = 'Aucun étudiant trouvé';
$string['all'] = 'Tous';
$string['filter'] = 'Filtrer';
$string['allstudents'] = 'Tous les étudiants';
$string['location'] = 'Lieu';
$string['reminders'] = 'Rappels automatiques';
$string['reminders_help'] = 'Créer des rappels automatiques avant l\'événement';
$string['eventdate_help'] = 'Date et heure de l\'événement ou de l\'échéance';
$string['selectusers'] = 'Veuillez sélectionner au moins un utilisateur';
$string['selectatleastonechannel'] = 'Veuillez sélectionner au moins un canal de communication';
$string['createalertdesc'] = 'Créez une alerte manuelle pour informer les étudiants d\'un examen, devoir, ou événement important.';
$string['viewalerts'] = 'Historique des alertes';
$string['recentalerts'] = 'Alertes récentes';
$string['noalerts'] = 'Aucune alerte trouvée';
$string['sentby'] = 'Envoyé par';
$string['timecreated'] = 'Date de création';
$string['back'] = 'Retour';
$string['view'] = 'Voir';
$string['choosedots'] = 'Choisir...';

// Course settings.
$string['coursesettings'] = 'Paramètres du cours';
$string['coursesettingsdesc'] = 'Configurez Student Monitor pour ce cours spécifique';
$string['generalsection'] = 'Paramètres généraux';
$string['newcontentnotifications'] = 'Notifications de nouveau contenu';
$string['notifynewcontent'] = 'Notifier le nouveau contenu pédagogique';
$string['notifynewcontent_help'] = 'Envoyer des notifications lorsque de nouveau contenu est ajouté au cours';
$string['activitytypes'] = 'Types d\'activités à surveiller';
$string['activity_assign'] = 'Devoirs';
$string['activity_quiz'] = 'Tests';
$string['activity_forum'] = 'Forums';
$string['activity_resource'] = 'Ressources';
$string['activity_url'] = 'URLs';
$string['activity_page'] = 'Pages';
$string['assignmentreminderssection'] = 'Rappels de devoirs';
$string['reminderdays_custom'] = 'Jours de rappel personnalisés';
$string['reminderdays_custom_help'] = 'Liste des jours avant échéance (ex: 7,3,1)';
$string['inactivitymonitoringsection'] = 'Surveillance d\'inactivité';
$string['monitorinactivity'] = 'Surveiller l\'inactivité';
$string['monitorinactivity_help'] = 'Activer la détection d\'inactivité pour ce cours';
$string['inactivitythreshold_custom'] = 'Seuil d\'inactivité personnalisé (jours)';
$string['inactivitythreshold_custom_help'] = 'Nombre de jours d\'inactivité avant alerte';
$string['supervisorssection'] = 'Superviseurs';
$string['defaultsupervisor'] = 'Superviseur par défaut';
$string['defaultsupervisor_help'] = 'Superviseur assigné automatiquement aux étudiants à risque';
$string['notificationpreferencessection'] = 'Préférences de notification';
$string['teacherdigest'] = 'Résumé pour enseignants';
$string['teacherdigest_help'] = 'Envoyer un résumé périodique aux enseignants';
$string['digestfrequency'] = 'Fréquence du résumé';
$string['digestfrequency_help'] = 'À quelle fréquence envoyer le résumé';
$string['digest_daily'] = 'Quotidien';
$string['digest_weekly'] = 'Hebdomadaire';
$string['digest_monthly'] = 'Mensuel';

// Student preferences.
$string['notificationpreferences'] = 'Préférences de notification';
$string['notificationpreferencesdesc'] = 'Gérez comment vous souhaitez recevoir les notifications Student Monitor';
$string['channelpreferences'] = 'Canaux de réception';
$string['channelpreferences_help'] = 'Sélectionnez les canaux par lesquels vous souhaitez recevoir les notifications';
$string['receivevia'] = 'Recevoir via';
$string['channel_email'] = 'Email';
$string['channel_moodle'] = 'Notification Moodle';
$string['channel_sms'] = 'SMS';
$string['channel_whatsapp'] = 'WhatsApp';
$string['notificationhistory'] = 'Historique des notifications';
$string['nonotifications'] = 'Aucune notification trouvée';
$string['subject'] = 'Objet';
$string['timesent'] = 'Date d\'envoi';
$string['preferencessaved'] = 'Préférences enregistrées avec succès';
$string['recipients_all_students'] = 'Tous les étudiants';

// Advanced reports (Phase 4).
$string['advancedreports'] = 'Rapports avancés';
$string['riskdistribution'] = 'Répartition des risques';
$string['notificationtrends'] = 'Tendances des notifications';
$string['notificationtypes'] = 'Types de notifications';
$string['interventionsbyrisk'] = 'Interventions par risque';
$string['totalstudents'] = 'Total étudiants';
$string['totalnotifications'] = 'Total notifications';
$string['last30days'] = 'Derniers 30 jours';
$string['criticalandhigh'] = 'Critique et Élevé';
$string['notificationsread'] = 'Notifications lues';
$string['notifications'] = 'Notifications';
$string['interventions'] = 'Interventions';
$string['backtodashboard'] = 'Retour au tableau de bord';
$string['exportstudents'] = 'Exporter étudiants';
$string['exportnotifications'] = 'Exporter notifications';

// Bulk actions (Phase 4).
$string['bulkactions'] = 'Actions en masse';
$string['bulkactionsdesc'] = 'Effectuez des actions sur plusieurs étudiants simultanément';
$string['selectaction'] = 'Sélectionner une action';
$string['bulkaction_assign'] = 'Assigner à un superviseur';
$string['bulkaction_unassign'] = 'Retirer l\'assignation';
$string['bulkaction_addnote'] = 'Ajouter une note';
$string['bulkaction_notify'] = 'Envoyer une notification';
$string['selectsupervisor'] = 'Sélectionner un superviseur';
$string['noteormessage'] = 'Note ou message';
$string['selectstudents'] = 'Sélectionner des étudiants';
$string['executeaction'] = 'Exécuter l\'action';
$string['confirmaction'] = 'Confirmer l\'action';
$string['confirmactionmsg'] = 'Vous êtes sur le point d\'exécuter "{action}" pour {count} étudiant(s). Continuer ?';
$string['bulkactionsuccess'] = '{success} action(s) réussie(s), {failed} échec(s)';
$string['bulknotificationsubject'] = 'Message groupé - Student Monitor';
$string['bulknotificationmessage'] = 'Ce message vous a été envoyé par votre superviseur pédagogique via Student Monitor.';

// Template editor (Phase 4).
$string['templateeditor'] = 'Éditeur de templates';
$string['templateeditordesc'] = 'Personnalisez les templates de notification envoyés aux étudiants';
$string['edittemplate'] = 'Modifier le template';
$string['body'] = 'Corps du message';
$string['availableplaceholders'] = 'Placeholders disponibles';
$string['placeholdersdesc'] = 'Vous pouvez utiliser ces placeholders dans le sujet et le corps du message. Ils seront remplacés automatiquement.';
$string['templatesaved'] = 'Template enregistré avec succès';
$string['templatedeleted'] = 'Template supprimé';
$string['templateresettodefault'] = 'Template réinitialisé aux valeurs par défaut';
$string['resettodefault'] = 'Réinitialiser';
$string['notemplates'] = 'Aucun template trouvé';
$string['templatetype'] = 'Type de template';
$string['lastmodified'] = 'Dernière modification';

// Advanced filters (Phase 4).
$string['advancedfilters'] = 'Filtres avancés';
$string['searchstudents'] = 'Rechercher des étudiants';
$string['searchplaceholder'] = 'Nom ou email...';
$string['filterbyinactivity'] = 'Filtrer par inactivité';
$string['filterbymissingassignments'] = 'Filtrer par devoirs manquants';
$string['filterbyassigned'] = 'Filtrer par assignation';
$string['assigned'] = 'Assigné';
$string['unassigned'] = 'Non assigné';
$string['clearfilters'] = 'Effacer les filtres';
$string['visiblestudents'] = 'Étudiants visibles';
$string['selectallvisible'] = 'Tout sélectionner (visibles)';
$string['email'] = 'Email';
