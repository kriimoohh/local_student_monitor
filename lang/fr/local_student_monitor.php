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

// Missing activities thresholds.
$string['missingactivitiessettings'] = 'Seuils d\'activités manquantes';
$string['missingactivitiessettingsdesc'] = 'Configurez les seuils de détection de risque basés sur les activités manquantes';
$string['missingactivitiesthreshold1'] = 'Seuil niveau 1 (activités)';
$string['missingactivitiesthreshold1_desc'] = 'Nombre d\'activités manquantes pour un risque moyen (défaut : 1)';
$string['missingactivitiesthreshold2'] = 'Seuil niveau 2 (activités)';
$string['missingactivitiesthreshold2_desc'] = 'Nombre d\'activités manquantes pour un risque élevé (défaut : 3)';
$string['missingactivitiesthreshold3'] = 'Seuil niveau 3 (activités)';
$string['missingactivitiesthreshold3_desc'] = 'Nombre d\'activités manquantes pour un risque critique (défaut : 5)';
$string['missingactivities'] = 'Activités manquantes';

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
$string['channelmoodle'] = 'Activer la messagerie Moodle';
$string['channelmoodle_desc'] = 'Envoyer les alertes via la messagerie directe de Moodle';
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

// Email sender settings.
$string['emailsendersettings'] = 'Expéditeur des emails';
$string['emailsendersettingsdesc'] = 'Configurer l\'adresse et le nom de l\'expéditeur utilisés pour l\'envoi automatique des notifications par email';
$string['notificationfromemail'] = 'Adresse email d\'expédition';
$string['notificationfromemail_desc'] = 'Adresse email utilisée comme expéditeur pour les notifications automatiques. Laisser vide pour utiliser l\'adresse no-reply de Moodle';
$string['notificationfromname'] = 'Nom de l\'expéditeur';
$string['notificationfromname_desc'] = 'Nom affiché comme expéditeur des notifications automatiques (appliqué uniquement si une adresse email d\'expédition est définie)';

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
$string['risk_faible'] = 'LOW';
$string['risk_moyen'] = 'MEDIUM';
$string['risk_eleve'] = 'HIGH';
$string['risk_critique'] = 'CRITICAL';
$string['risk_low'] = 'Low';
$string['risk_medium'] = 'Medium';
$string['risk_high'] = 'High';
$string['risk_critical'] = 'Critical';
$string['triggercriterion'] = 'Critère déclencheur';
$string['trigger_inactivity'] = 'Inactivité';
$string['trigger_activities'] = 'Activités manquantes';
$string['trigger_both'] = 'Inactivité et activités';

// Paramètres de l\'institution.
$string['institutionname'] = 'Nom de l\'institution';
$string['institutionname_desc'] = 'Nom de votre institution (affiché dans les notifications)';

// Notification types.
$string['notificationtype'] = 'Type de notification';
$string['inactivitylevel1'] = 'Inactivité niveau 1';
$string['inactivitylevel2'] = 'Inactivité niveau 2';
$string['inactivitylevel3'] = 'Inactivité niveau 3';
$string['newcontent'] = 'Nouveau contenu';
$string['assignmentreminder'] = 'Rappel de devoir';
$string['institutionalannouncement'] = 'Annonce institutionnelle';
$string['manualalert'] = 'Alerte manuelle';
$string['manual_alert'] = 'Alerte manuelle';
$string['assignment_reminder'] = 'Rappel de devoir';
$string['assignment_reminder_7days'] = 'Rappel de devoir (J-7)';
$string['assignment_reminder_1day'] = 'Rappel de devoir (J-1)';
$string['institutional_announcement'] = 'Annonce institutionnelle';
$string['new_content'] = 'Nouveau contenu';
$string['forum_announcement'] = 'Annonce de forum';

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
$string['recipients_category'] = 'Tous les étudiants d\'une catégorie';
$string['recipients_all_course'] = 'Tout le cours';
$string['recipients_group'] = 'Groupe spécifique';
$string['recipients_manual'] = 'Sélection manuelle';
$string['recipients_csv'] = 'Importer depuis un fichier CSV';
$string['csvfile'] = 'Fichier CSV des destinataires';
$string['csvfile_help'] = 'Téléchargez un fichier CSV contenant les emails, noms d\'utilisateur ou IDs des destinataires. Format : un destinataire par ligne. Exemple :<br>email@example.com<br>username123<br>12345';
$string['csvfilerequired'] = 'Veuillez télécharger un fichier CSV';
$string['reminder7days'] = 'Rappel J-7';
$string['reminder3days'] = 'Rappel J-3';
$string['reminder1day'] = 'Rappel J-1';
$string['sendalert'] = 'Envoyer l\'alerte';

// Student tracking.
$string['studentname'] = 'Nom de l\'étudiant';
$string['lastactivity'] = 'Dernière activité';
$string['inactivitydays'] = 'Jours d\'inactivité';
$string['missingassignments'] = 'Activités manquantes';
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
$string['alertsqueued'] = '{$a} alerte(s) en cours d\'envoi en arrière-plan.';
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
$string['student'] = 'étudiant';
$string['students'] = 'étudiants';
$string['nostudents'] = 'Aucun étudiant trouvé';
$string['all'] = 'Tous';
$string['filter'] = 'Filtrer';
$string['allstudents'] = 'Tous les étudiants';
$string['location'] = 'Lieu';
$string['reminders'] = 'Rappels automatiques';
$string['reminders_help'] = 'Créer des rappels automatiques avant l\'événement';
$string['eventdate_help'] = 'Date et heure de l\'événement ou de l\'échéance';
$string['selectusers'] = 'Veuillez sélectionner au moins un utilisateur';
$string['selectusersfield'] = 'Sélectionner des utilisateurs';
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
$string['criticalandhigh'] = 'Critical et High';
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
$string['filterbymissingassignments'] = 'Filtrer par activités manquantes';
$string['filterbyassigned'] = 'Filtrer par assignation';
$string['apply'] = 'Appliquer';
$string['assigned'] = 'Assigné';
$string['unassigned'] = 'Non assigné';
$string['clearfilters'] = 'Effacer les filtres';
$string['visiblestudents'] = 'Étudiants visibles';
$string['selectallvisible'] = 'Tout sélectionner (visibles)';
$string['email'] = 'Email';

// PDF Export & Communication (Phase 5).
$string['studentreport'] = 'Rapport des étudiants';
$string['studentmonitorreport'] = 'Rapport Student Monitor';
$string['generatedon'] = 'Généré le';
$string['summary'] = 'Résumé';
$string['detailedreport'] = 'Rapport détaillé';
$string['studentmonitordetailedreport'] = 'Rapport détaillé Student Monitor';
$string['overview'] = 'Vue d\'ensemble';
$string['generatedby'] = 'Généré par';
$string['total'] = 'Total';
$string['student'] = 'Étudiant';

// Communication statistics (Phase 5).
$string['communicationstats'] = 'Statistiques de communication';
$string['period'] = 'Période';
$string['thisweek'] = 'Cette semaine';
$string['thismonth'] = 'Ce mois';
$string['thisyear'] = 'Cette année';
$string['totalsmssent'] = 'Total SMS envoyés';
$string['parts'] = 'parties';
$string['totalcost'] = 'Coût total';
$string['currentperiod'] = 'Période actuelle';
$string['avgcostpersms'] = 'Coût moyen par SMS';
$string['monthlybudget'] = 'Budget mensuel';
$string['dailysmscosts'] = 'Coûts SMS quotidiens';
$string['costbytype'] = 'Coûts par type';
$string['channeldistribution'] = 'Répartition par canal';
$string['channel'] = 'Canal';
$string['count'] = 'Nombre';
$string['nodata'] = 'Aucune donnée disponible';

// PDF export actions.
$string['exportstudentspdf'] = 'Exporter étudiants (PDF)';
$string['exportnotificationspdf'] = 'Exporter notifications (PDF)';
$string['exportdetailedpdf'] = 'Exporter rapport détaillé (PDF)';
$string['invalidexporttype'] = 'Type d\'export invalide';

// Workflow automation & Tasks (Phase 6).
$string['taskmanagement'] = 'Gestion des tâches';
$string['taskcompleted'] = 'Tâche marquée comme terminée';
$string['taskdeferred'] = 'Tâche reportée';
$string['taskreassigned'] = 'Tâche réassignée';
$string['notasksfound'] = 'Aucune tâche trouvée';
$string['tasktype'] = 'Type de tâche';
$string['duedate'] = 'Échéance';
$string['actions'] = 'Actions';
$string['totaltasks'] = 'Total des tâches';
$string['pendingtasks'] = 'Tâches en attente';
$string['inprogresstasks'] = 'Tâches en cours';
$string['overduetasks'] = 'Tâches en retard';
$string['filterbystatus'] = 'Filtrer par statut';
$string['all'] = 'Tous';
$string['pending'] = 'En attente';
$string['inprogress'] = 'En cours';
$string['completed'] = 'Terminé';
$string['overdue'] = 'En retard';
$string['startwork'] = 'Commencer';
$string['markcomplete'] = 'Marquer comme terminé';
$string['viewdetails'] = 'Voir détails';

// Task types.
$string['tasktype_urgent_intervention'] = 'Intervention urgente';
$string['tasktype_follow_up'] = 'Suivi';
$string['tasktype_preventive'] = 'Préventif';
$string['tasktype_check_in'] = 'Point de contrôle';

// Task priorities.
$string['priority'] = 'Priorité';
$string['priority_urgent'] = 'Urgent';
$string['priority_high'] = 'Élevée';
$string['priority_normal'] = 'Normale';
$string['priority_low'] = 'Basse';

// Task statuses.
$string['status'] = 'Statut';
$string['status_pending'] = 'En attente';
$string['status_in_progress'] = 'En cours';
$string['status_completed'] = 'Terminé';

// Intervention tracking.
$string['interventionlogged'] = 'Intervention enregistrée';
$string['interventiontype'] = 'Type d\'intervention';
$string['interventionnotes'] = 'Notes d\'intervention';
$string['interventionhistory'] = 'Historique des interventions';
$string['lastintervention'] = 'Dernière intervention';
$string['interventioncount'] = 'Nombre d\'interventions';
$string['task_completed'] = 'Tâche terminée';
$string['phone_call'] = 'Appel téléphonique';
$string['meeting'] = 'Réunion';
$string['email_response'] = 'Réponse email';

// Business rules.
$string['businessrules'] = 'Règles métier';
$string['rulename'] = 'Nom de la règle';
$string['ruleconditions'] = 'Conditions';
$string['ruleactions'] = 'Actions';
$string['ruleenabled'] = 'Règle activée';
$string['ruledisabled'] = 'Règle désactivée';
$string['ruleexecuted'] = 'Règle exécutée';
$string['createrule'] = 'Créer une règle';
$string['testrule'] = 'Tester la règle';

// Effectiveness reports.
$string['effectivenessreports'] = 'Rapports d\'efficacité';
$string['overalleffectiveness'] = 'Efficacité globale';
$string['studentsimproved'] = 'Étudiants ayant progressé';
$string['successrate'] = 'Taux de réussite';
$string['avginterventions'] = 'Interventions moyennes';
$string['perstudent'] = 'par étudiant';
$string['supervisorperformance'] = 'Performance du superviseur';
$string['taskscompleted'] = 'Tâches terminées';
$string['taskspending'] = 'Tâches en attente';
$string['tasksoverdue'] = 'Tâches en retard';
$string['avgresponsetime'] = 'Temps de réponse moyen';
$string['hours'] = 'heures';
$string['risktransitions'] = 'Transitions de risque';
$string['interventiontypes'] = 'Types d\'intervention';
$string['improved'] = 'Amélioré';
$string['stable'] = 'Stable';
$string['deteriorated'] = 'Détérioré';
$string['thisquarter'] = 'Ce trimestre';
$string['allsupervisors'] = 'Tous les superviseurs';

// Workflow messages.
$string['urgentintervention'] = 'Intervention urgente requise';
$string['criticalriskmessage'] = 'Votre niveau de risque académique est critique. Veuillez contacter immédiatement votre superviseur.';
$string['followupreminder'] = 'Rappel de suivi';
$string['highriskmessage'] = 'Votre activité académique nécessite une attention. Veuillez vous reconnecter à votre cours.';
$string['preventivereminder'] = 'Rappel préventif';
$string['mediumriskmessage'] = 'Nous avons remarqué une baisse de votre activité. N\'hésitez pas à nous contacter si vous avez besoin d\'aide.';
$string['escalationsubject'] = 'Escalade - Étudiant en situation critique';
$string['escalationmessage'] = 'L\'étudiant {$a->studentname} est en situation critique (Niveau: {$a->risklevel}). Inactivité: {$a->inactivity} jours, Activités manquantes: {$a->missing}.';
$string['automatednotification'] = 'Notification automatique';
$string['risknotificationmessage'] = 'Alerte automatique: Niveau de risque {$a->risklevel}, Inactivité: {$a->inactivity} jours.';
$string['supervisornotification'] = 'Notification superviseur';
$string['studentneedsattention'] = 'L\'étudiant {$a->studentname} nécessite votre attention (Niveau: {$a->risklevel}).';
$string['systemalert'] = 'Alerte système Student Monitor';
$string['taskreassignedsubject'] = 'Nouvelle tâche assignée';
$string['taskreassignedmessage'] = 'Une tâche de type {$a->tasktype} vous a été assignée. Échéance: {$a->duedate}.';

// Supervisor settings.
$string['supervisor'] = 'Superviseur';
$string['defaultsupervisor'] = 'Superviseur par défaut';
$string['assignsupervisor'] = 'Assigner un superviseur';
$string['coordinatoremail'] = 'Email du coordinateur';
$string['coordinatoremail_desc'] = 'Email du coordinateur académique pour les escalades';

// Type labels.
$string['type'] = 'Type';

// Alert types and statuses.
$string['manual'] = 'Manuelle';
$string['automatic'] = 'Automatique';
$string['system'] = 'Système';
$string['failed'] = 'Échec';
$string['sent'] = 'Envoyé';
$string['message'] = 'Message';

// Report Scheduler.
$string['reportschedules'] = 'Planifications de rapports';
$string['createnewschedule'] = 'Créer une nouvelle planification';
$string['noschedules'] = 'Aucune planification créée';
$string['reporttype'] = 'Type de rapport';
$string['frequency'] = 'Fréquence';
$string['format'] = 'Format';
$string['lastrun'] = 'Dernière exécution';
$string['nextrun'] = 'Prochaine exécution';
$string['scheduledeleted'] = 'Planification supprimée';
$string['confirmdelete'] = 'Êtes-vous sûr de vouloir supprimer?';
$string['enabled'] = 'Activé';
$string['disabled'] = 'Désactivé';
$string['enable'] = 'Activer';
$string['disable'] = 'Désactiver';

// Report types.
$string['report_executive_summary'] = 'Résumé exécutif';
$string['report_supervisor_performance'] = 'Performance des superviseurs';
$string['report_student_risk'] = 'Risques étudiants';
$string['report_retention'] = 'Rétention';
$string['report_cohort_analysis'] = 'Analyse de cohortes';

// Frequencies.
$string['freq_daily'] = 'Quotidien';
$string['freq_weekly'] = 'Hebdomadaire';
$string['freq_monthly'] = 'Mensuel';
$string['freq_quarterly'] = 'Trimestriel';

// Misc.
$string['scheduledreport'] = 'Rapport programmé';
$string['scheduledreportbody'] = 'Veuillez trouver ci-joint le rapport {$a->reporttype} généré le {$a->date}.';
$string['generatedon'] = 'Généré le';
$string['critical'] = 'Critical';
$string['high'] = 'High';
$string['medium'] = 'Medium';
$string['low'] = 'Low';

// Students at risk page.
$string['viewstudents'] = 'Voir les étudiants';
$string['currentfilter'] = 'Filtre actuel';
$string['clearfilter'] = 'Effacer le filtre';
$string['showingatrisk'] = 'Affichage des étudiants à risque (MEDIUM et plus)';
$string['nostudentsatrisk'] = 'Aucun étudiant à risque trouvé - Excellent travail !';
$string['viewprofile'] = 'Voir le profil';
$string['sendnotification'] = 'Envoyer une notification';
$string['perpage'] = 'Par page';

// Recipients by inactivity level.
$string['recipients_by_inactivity_level'] = 'Par niveau d\'inactivité / risque';
$string['selectinactivitylevel'] = 'Sélectionner le niveau';
$string['inactivity_level'] = 'Niveau d\'inactivité / risque';
$string['inactivity_level_help'] = 'Sélectionnez le niveau d\'inactivité ou de risque pour envoyer l\'alerte aux étudiants correspondants.';
$string['inactivity_level1'] = 'Niveau 1 - Inactivité modérée';
$string['inactivity_level2'] = 'Niveau 2 - Inactivité importante';
$string['inactivity_level3'] = 'Niveau 3 - Inactivité critique';
$string['inactivitydays_3plus'] = '3+ jours';
$string['inactivitydays_7plus'] = '7+ jours';
$string['inactivitydays_14plus'] = '14+ jours';
$string['studentpreview'] = 'Aperçu des étudiants';

// Automatic alerts configuration.
$string['configureautomaticalerts'] = 'Configuration des alertes automatiques';
$string['configureautomaticalertsdesc'] = 'Configurez les paramètres des alertes automatiques pour le suivi des étudiants à risque.';
$string['automaticalertsenabled'] = 'Alertes automatiques activées';
$string['automaticalertsdisabled'] = 'Alertes automatiques désactivées';
$string['automaticalertsenabledinfo'] = 'Les alertes automatiques sont actives. Le système surveille les étudiants et envoie des notifications automatiques selon les seuils configurés.';
$string['automaticalertsdisabledinfo'] = 'Les alertes automatiques sont désactivées. Activez-les pour commencer à surveiller les étudiants automatiquement.';
$string['automaticalertsinfo'] = 'Système de détection et de notification automatique des étudiants à risque.';
$string['enableautomaticalerts'] = 'Activer les alertes automatiques';
$string['disableautomaticalerts'] = 'Désactiver les alertes automatiques';
$string['confirmdisableautomaticalerts'] = 'Êtes-vous sûr de vouloir désactiver les alertes automatiques ? Les étudiants à risque ne recevront plus de notifications automatiques.';
$string['currentstatus'] = 'Statut actuel';
$string['inactivitythresholds'] = 'Seuils d\'inactivité';
$string['savethresholds'] = 'Enregistrer les seuils';
$string['thresholdssaved'] = 'Seuils d\'inactivité enregistrés avec succès';
$string['notificationchannels'] = 'Canaux de notification';
$string['savechannels'] = 'Enregistrer les canaux';
$string['channelssaved'] = 'Canaux de notification enregistrés avec succès';
$string['channelsconfigurationdesc'] = 'Sélectionnez les canaux à utiliser pour les alertes automatiques. Au moins un canal doit être activé.';
$string['configurealerts'] = 'Configurer les alertes';

// Preview alert.
$string['previewalert'] = 'Prévisualiser l\'alerte';
$string['previewalertdesc'] = 'Vérifiez les détails de l\'alerte avant l\'envoi. Contrôlez les destinataires, l\'objet, le message et les canaux.';
$string['backtoedit'] = 'Retour à l\'édition';
$string['confirmsendalert'] = 'Êtes-vous sûr de vouloir envoyer cette alerte ?';
$string['sender'] = 'Expéditeur';
$string['recipientslist'] = 'Liste des destinataires';
$string['andmore'] = 'et {$a} de plus';
$string['error_no_preview_data'] = 'Aucune donnée de prévisualisation disponible. Veuillez d\'abord créer une alerte.';

// Refresh tracking.
$string['refreshtracking'] = 'Actualiser l\'analyse des étudiants';
$string['refreshtrackingbtn'] = '🔄 Actualiser l\'analyse';
$string['refreshtrackingconfirm'] = 'Voulez-vous lancer une analyse complète de tous les étudiants ?';
$string['refreshtrackinginfo'] = 'Cette opération va recalculer les niveaux de risque, les jours d\'inactivité et les activités manquantes pour tous les étudiants actifs. Cela peut prendre plusieurs minutes selon le nombre d\'étudiants.';
$string['refreshtrackingsuccess'] = 'Analyse terminée : {$a->updated}/{$a->total} étudiants mis à jour ({$a->failed} échecs). {$a->courses} suivis de cours actualisés.';

// Weekly report strings.
$string['automaticalerts_label'] = 'Alertes automatiques';
$string['manualalerts_label'] = 'Alertes manuelles';
$string['autoalertdetails'] = 'Détail des alertes automatiques';
$string['additionalstatistics'] = 'Statistiques supplémentaires';
$string['averageinactivity'] = 'Inactivité moyenne';
$string['days_unit'] = 'jours';
$string['inactivityleveltype'] = 'Inactivité niveau {$a}';
$string['assignmentremindertype'] = 'Rappel de devoir';
$string['risktype'] = 'Risque {$a}';
$string['totalcount'] = 'Total';
$string['sentcount'] = 'Envoyées';
$string['readcount'] = 'Lues';
$string['failedcount'] = 'Échecs';

// CSV export headers.
$string['csv_firstname'] = 'Prénom';
$string['csv_lastname'] = 'Nom';
$string['csv_risklevel'] = 'Niveau de risque';
$string['csv_inactivitydays'] = 'Jours d\'inactivité';
$string['csv_missingactivities'] = 'Activités manquantes';
$string['csv_notificationssent'] = 'Notifications envoyées';
$string['csv_interventionneeded'] = 'Intervention nécessaire';
$string['csv_lastactivity'] = 'Dernière activité';
$string['csv_lastupdated'] = 'Dernière mise à jour';
$string['yes'] = 'Oui';
$string['no'] = 'Non';
$string['never'] = 'Jamais';
