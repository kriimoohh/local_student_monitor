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
$string['alertssent'] = '{$a} alerte(s) envoyée(s) avec succès';
$string['alertsfailed'] = '{$a} alerte(s) n\'ont pas pu être envoyée(s)';
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
$string['escalationmessage'] = 'L\'étudiant {$a->studentname} est en situation critique (Niveau: {$a->risklevel}). Inactivité: {$a->inactivity} jours, Devoirs manquants: {$a->missing}.';
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

// Predictive analytics (Phase 7).
$string['predictiveanalytics'] = 'Analytics prédictifs';
$string['predictionhorizon'] = 'Horizon de prédiction';
$string['totalpredictions'] = 'Total prédictions';
$string['earlywarnings'] = 'Alertes précoces';
$string['atriskpredicted'] = 'à risque prédits';
$string['avgconfidence'] = 'Confiance moyenne';
$string['deterioratingtrend'] = 'Tendance détériorée';
$string['ofstudents'] = 'des étudiants';
$string['predictedriskdistribution'] = 'Distribution du risque prédit';
$string['trenddirection'] = 'Direction de tendance';
$string['currentrisk'] = 'Risque actuel';
$string['predictedrisk'] = 'Risque prédit';
$string['confidence'] = 'Confiance';
$string['probability'] = 'Probabilité';
$string['trend'] = 'Tendance';
$string['keyfactors'] = 'Facteurs clés';
$string['noearlywarnings'] = 'Aucune alerte précoce détectée';
$string['predictiondetails'] = 'Détails de prédiction';
$string['predictionhorizoninfo'] = 'Prédictions pour les {$a} prochains jours';
$string['predictiondateinfo'] = 'Date de prédiction: {$a}';
$string['predictionmethodinfo'] = 'Méthode: Régression linéaire sur données historiques';
$string['predictionconfidenceinfo'] = 'Confiance basée sur qualité et quantité des données';
$string['days'] = 'jours';
$string['daysago'] = 'jours';

// Parent/Guardian management (Phase 7).
$string['parentmanagement'] = 'Gestion des parents/tuteurs';
$string['registeredparents'] = 'Parents enregistrés';
$string['notificationsthismonth'] = 'Notifications ce mois';
$string['uniqueparentsnotified'] = 'Parents uniques notifiés';
$string['addparent'] = 'Ajouter un parent/tuteur';
$string['parentname'] = 'Nom du parent';
$string['parentemail'] = 'Email du parent';
$string['parentphone'] = 'Téléphone du parent';
$string['relationship'] = 'Relation';
$string['parent'] = 'Parent';
$string['guardian'] = 'Tuteur';
$string['tutor'] = 'Tuteur académique';
$string['selectstudent'] = 'Sélectionner un étudiant';
$string['parentadded'] = 'Parent/tuteur ajouté avec succès';
$string['parentdeleted'] = 'Parent/tuteur supprimé';
$string['parentsnotified'] = 'parents/tuteurs notifiés';
$string['studentswitparents'] = 'Étudiants avec parents enregistrés';
$string['noparentsregistered'] = 'Aucun parent/tuteur enregistré';
$string['notifyparents'] = 'Notifier les parents';
$string['parentnotificationsubject'] = 'Information importante concernant votre enfant';
$string['parentnotificationtemplate'] = 'Bonjour {$a->parentname},\n\nNous vous contactons concernant {$a->studentname}.\n\nNiveau de risque: {$a->risklevel}\nJours d\'inactivité: {$a->inactivitydays}\nDevoirs manquants: {$a->missingassignments}\n\nContact support: {$a->supportemail} / {$a->supportphone}';
$string['recommendations'] = 'Recommandations';
$string['recommendcontactstudent'] = 'Contacter votre enfant pour comprendre la situation';
$string['recommendassignmenthelp'] = 'Aider avec les devoirs en retard';
$string['recommendurgencontact'] = 'Contacter immédiatement votre enfant';
$string['recommendcontactsupervisor'] = 'Contacter le superviseur académique';
$string['recommendencouragement'] = 'Encourager la persévérance';
$string['parentsmstemplate'] = 'ALERTE: {$a->studentname} - Niveau: {$a->risklevel}. Contactez le support.';
$string['weeklydigestsubject'] = 'Résumé hebdomadaire';
$string['weeklydigestintro'] = 'Bonjour {$a->parentname},\n\nVoici le résumé hebdomadaire pour {$a->studentname}:';
$string['weeklyactivitysummary'] = 'Résumé d\'activité';
$string['lastlogin'] = 'Dernière connexion';

// Custom report builder (Phase 7).
$string['customreportbuilder'] = 'Constructeur de rapports';
$string['createcustomreport'] = 'Créer un rapport personnalisé';
$string['savedreports'] = 'Rapports sauvegardés';
$string['selectcolumns'] = 'Sélectionner les colonnes';
$string['selectfilters'] = 'Sélectionner les filtres';
$string['reportname'] = 'Nom du rapport';
$string['savereport'] = 'Sauvegarder le rapport';
$string['runreport'] = 'Exécuter le rapport';
$string['deletereport'] = 'Supprimer le rapport';
$string['exportreport'] = 'Exporter le rapport';
$string['reportstatistics'] = 'Statistiques du rapport';
$string['column_student_name'] = 'Nom étudiant';
$string['column_student_email'] = 'Email étudiant';
$string['column_risk_level'] = 'Niveau de risque';
$string['column_inactivity_days'] = 'Jours d\'inactivité';
$string['column_missing_assignments'] = 'Devoirs manquants';
$string['column_notification_count'] = 'Notifications envoyées';
$string['column_last_login'] = 'Dernière connexion';
$string['column_assigned_to'] = 'Superviseur assigné';
$string['column_intervention_count'] = 'Interventions';
$string['column_last_intervention'] = 'Dernière intervention';
$string['column_grade_average'] = 'Moyenne générale';
$string['column_course_count'] = 'Cours inscrits';
$string['column_predicted_risk'] = 'Risque prédit';

// Email campaigns (Phase 8).
$string['emailcampaigns'] = 'Campagnes email';
$string['createnewcampaign'] = 'Créer une nouvelle campagne';
$string['campaignname'] = 'Nom de campagne';
$string['subject'] = 'Sujet';
$string['message'] = 'Message';
$string['targetaudience'] = 'Audience cible';
$string['scheduledtime'] = 'Heure programmée';
$string['abtesting'] = 'Test A/B';
$string['recipients'] = 'Destinataires';
$string['campaignsent'] = 'Campagne envoyée: {$a->sent} réussis, {$a->failed} échoués';
$string['campaigndeleted'] = 'Campagne supprimée';
$string['totalcampaigns'] = 'Total campagnes';
$string['campaignssent'] = 'Campagnes envoyées';
$string['drafts'] = 'Brouillons';
$string['scheduled'] = 'Programmées';
$string['status_draft'] = 'Brouillon';
$string['status_scheduled'] = 'Programmée';
$string['status_sending'] = 'Envoi en cours';
$string['status_sent'] = 'Envoyée';
$string['send'] = 'Envoyer';
$string['viewstats'] = 'Voir les stats';
$string['confirmdeletecampaign'] = 'Êtes-vous sûr de vouloir supprimer cette campagne?';
$string['nocampaigns'] = 'Aucune campagne créée';
$string['backtocampaigns'] = 'Retour aux campagnes';

// Campaign statistics (Phase 8).
$string['campaignstatistics'] = 'Statistiques de campagne';
$string['totalsent'] = 'Total envoyé';
$string['openrate'] = 'Taux d\'ouverture';
$string['clickrate'] = 'Taux de clics';
$string['conversionrate'] = 'Taux de conversion';
$string['opens'] = 'ouvertures';
$string['clicks'] = 'clics';
$string['conversions'] = 'conversions';
$string['senttime'] = 'Date d\'envoi';
$string['abtestingresults'] = 'Résultats du test A/B';
$string['variant'] = 'Variante';
$string['sent'] = 'Envoyé';
$string['opened'] = 'Ouvert';
$string['clicked'] = 'Cliqué';
$string['converted'] = 'Converti';
$string['winner'] = 'Gagnant';
$string['tie'] = 'Égalité';
$string['performancedifference'] = 'Différence de performance: {$a->difference}%';
$string['performancecharts'] = 'Graphiques de performance';
$string['conversionfunnel'] = 'Entonnoir de conversion';
$string['abcomparison'] = 'Comparaison A/B';
$string['recipientbreakdown'] = 'Détails des destinataires';
$string['recipient'] = 'Destinataire';
$string['exportoptions'] = 'Options d\'export';
$string['exporttocsv'] = 'Exporter en CSV';
$string['norecipients'] = 'Aucun destinataire';

// Gamification (Phase 8).
$string['gamification'] = 'Gamification';
$string['leaderboard'] = 'Classement';
$string['points'] = 'Points';
$string['level'] = 'Niveau';
$string['achievements'] = 'Succès';
$string['currentstreak'] = 'Série actuelle';
$string['longeststreak'] = 'Meilleure série';
$string['yourstats'] = 'Vos statistiques';
$string['progresstonextlevel'] = 'Progression vers le niveau suivant';
$string['pointstonextlevel'] = '{$a->current} / {$a->needed} points';
$string['topleaders'] = 'Top classement';
$string['rank'] = 'Rang';
$string['student'] = 'Étudiant';
$string['streak'] = 'Série';
$string['you'] = 'Vous';
$string['leveln'] = 'Niveau {$a->level}';
$string['recentachievements'] = 'Succès récents';
$string['noachievements'] = 'Aucun succès récent';
$string['noleaderboarddata'] = 'Aucune donnée de classement';
$string['earned'] = 'a gagné';
$string['period_all'] = 'Tout';
$string['period_month'] = 'Ce mois';
$string['period_week'] = 'Cette semaine';

// Achievement names (Phase 8).
$string['achievement_first_login'] = 'Premier pas';
$string['achievement_week_streak'] = 'Semaine assidue';
$string['achievement_month_streak'] = 'Mois complet';
$string['achievement_all_assignments'] = 'Tous les devoirs';
$string['achievement_early_submitter'] = 'Soumission anticipée';
$string['achievement_helper'] = 'Bon camarade';
$string['achievement_improvement'] = 'Amélioration notable';
$string['achievement_risk_recovery'] = 'Remontée spectaculaire';

// Mobile API (Phase 8).
$string['mobileapi'] = 'API mobile';
$string['apienabled'] = 'API activée';
$string['apikey'] = 'Clé API';
$string['apidocumentation'] = 'Documentation API';
$string['endpoints'] = 'Points d\'accès';
$string['endpoint_getstats'] = 'Obtenir les statistiques étudiant';
$string['endpoint_getgamification'] = 'Obtenir les données de gamification';
$string['endpoint_getleaderboard'] = 'Obtenir le classement';
$string['endpoint_getcampaignstats'] = 'Obtenir les statistiques de campagne';

// Additional strings (Phase 8).
$string['backtodashboard'] = 'Retour au tableau de bord';
$string['filter_risklevel'] = 'Niveau de risque';
$string['filter_inactivitydays'] = 'Jours d\'inactivité';
$string['filter_missingassignments'] = 'Devoirs manquants';
$string['filter_lastlogin'] = 'Dernière connexion';
$string['filter_supervisor'] = 'Superviseur';
$string['filter_course'] = 'Cours';
$string['campaign_create'] = 'Créer une campagne';
$string['campaign_edit'] = 'Modifier la campagne';
$string['campaign_delete'] = 'Supprimer la campagne';
$string['variant_a'] = 'Variante A';
$string['variant_b'] = 'Variante B';
$string['enable_abtesting'] = 'Activer le test A/B';
$string['abtest_splitratio'] = 'Ratio de division';
$string['target_all'] = 'Tous les étudiants';
$string['target_atrisk'] = 'Étudiants à risque';
$string['target_critical'] = 'Risque critique';
$string['target_high'] = 'Risque élevé';
$string['target_medium'] = 'Risque moyen';
$string['target_low'] = 'Risque faible';
$string['send_immediately'] = 'Envoyer immédiatement';
$string['schedule_later'] = 'Programmer pour plus tard';
$string['campaign_scheduled'] = 'Campagne programmée avec succès';
$string['campaign_created'] = 'Campagne créée avec succès';
$string['pointsawarded'] = '{$a} points attribués';
$string['levelup'] = 'Niveau augmenté! Nouveau niveau: {$a}';
$string['streakbonus'] = 'Bonus de série: +{$a} points';
$string['achievementunlocked'] = 'Succès débloqué: {$a}';

// Student Self-Service Portal & AI Recommendations (Phase 9).
$string['studentdashboard'] = 'Tableau de bord étudiant';
$string['welcomeback'] = 'Bienvenue {$a}!';
$string['yourrisk'] = 'Votre niveau de risque';
$string['yourpoints'] = 'Vos points';
$string['yourstreak'] = 'Votre série';
$string['noriskdata'] = 'Aucune donnée de risque disponible';
$string['personalizedrecommendations'] = 'Recommandations personnalisées';
$string['norecommendations'] = 'Aucune recommandation pour le moment';
$string['keepupgoodwork'] = 'Continuez votre excellent travail!';
$string['impact'] = 'Impact';
$string['takeaction'] = 'Passer à l\'action';
$string['yourprogress'] = 'Votre progression';
$string['activitythisweek'] = 'Activité cette semaine';
$string['performancetrend'] = 'Tendance de performance';
$string['quickactions'] = 'Actions rapides';
$string['viewleaderboard'] = 'Voir le classement';
$string['viewcalendar'] = 'Voir le calendrier';
$string['viewcourses'] = 'Voir mes cours';
$string['goto'] = 'Accéder';
$string['noachievementsyet'] = 'Pas encore de succès. Commencez à apprendre!';
$string['tipsandmotivation'] = 'Conseils et motivation';
$string['missing'] = 'manquant(s)';

// AI Recommendations.
$string['rec_increase_login'] = 'Augmentez votre fréquence de connexion';
$string['rec_increase_login_desc'] = 'Vous vous êtes connecté {$a->current} fois ce mois. Essayez d\'atteindre {$a->target} connexions pour rester engagé.';
$string['rec_study_consistency'] = 'Améliorez la régularité de vos études';
$string['rec_study_consistency_desc'] = 'Essayez de vous connecter plus régulièrement (au moins tous les 2 jours) pour maintenir un rythme d\'apprentissage constant.';
$string['rec_optimal_study_time'] = 'Optimisez votre horaire d\'étude';
$string['rec_optimal_study_time_desc'] = 'Envisagez d\'étudier pendant les heures de jour (8h-22h) pour une meilleure concentration.';
$string['rec_urgent_assignment'] = 'Devoir urgent à soumettre';
$string['rec_urgent_assignment_desc'] = '{$a->name} dans {$a->course} est dû le {$a->duedate}. Ne manquez pas cette échéance!';
$string['rec_explore_resources'] = 'Explorez les ressources non consultées';
$string['rec_explore_resources_desc'] = 'Vous avez {$a->count} ressources non consultées. Explorer ces contenus pourrait améliorer votre compréhension.';
$string['rec_forum_participation'] = 'Participez aux discussions de forum';
$string['rec_forum_participation_desc'] = 'Rejoignez les discussions pour apprendre de vos pairs et partager vos connaissances.';
$string['rec_help_peers'] = 'Aidez vos camarades';
$string['rec_help_peers_desc'] = 'Avec votre bonne performance, vous pouvez aider d\'autres étudiants dans les forums. C\'est bon pour l\'apprentissage!';
$string['rec_catch_up_plan'] = 'Plan de rattrapage nécessaire';
$string['rec_catch_up_plan_desc'] = 'Vous avez {$a->count} devoirs en retard. Créez un plan pour les rattraper progressivement.';
$string['rec_use_calendar'] = 'Utilisez le calendrier Moodle';
$string['rec_use_calendar_desc'] = 'Le calendrier vous aide à rester organisé et à ne manquer aucune échéance.';
$string['rec_increase_engagement'] = 'Augmentez votre engagement';
$string['rec_increase_engagement_desc'] = 'Votre activité hebdomadaire est de {$a->current}. Essayez d\'atteindre {$a->target} activités par semaine.';
$string['rec_check_leaderboard'] = 'Consultez le classement';
$string['rec_check_leaderboard_desc'] = 'Vous êtes actif! Vérifiez votre position dans le classement et gagnez plus de points.';

// Peer comparison.
$string['peercomparison'] = 'Comparaison avec les pairs';
$string['peercomparison_desc'] = 'Comparez anonymement vos performances avec d\'autres étudiants de vos cours.';
$string['yourperformance'] = 'Votre performance';
$string['percentile'] = 'e percentile';
$string['comparedto'] = 'Comparé à {$a} autres étudiants de vos cours';
$string['performanceradar'] = 'Radar de performance';
$string['detailedmetrics'] = 'Métriques détaillées';
$string['loginfrequency'] = 'Fréquence de connexion';
$string['assignmentcompletion'] = 'Completion des devoirs';
$string['engagement'] = 'Engagement';
$string['gradeperformance'] = 'Performance notes';
$string['yourvalue'] = 'Votre valeur';
$string['peeraverage'] = 'Moyenne des pairs';
$string['percentileposition'] = 'Position percentile';
$string['logins'] = 'connexions';
$string['activities'] = 'activités';
$string['insights'] = 'Analyses';
$string['category_top'] = 'Performance exceptionnelle';
$string['category_above_average'] = 'Au-dessus de la moyenne';
$string['category_average'] = 'Performance moyenne';
$string['category_below_average'] = 'En dessous de la moyenne';
$string['category_needs_improvement'] = 'Nécessite amélioration';
$string['insight_top_performer'] = 'Félicitations! Vous êtes dans le top 25% de vos pairs. Excellent travail!';
$string['insight_above_average'] = 'Vous êtes au-dessus de la moyenne. Continuez comme ça!';
$string['insight_room_for_improvement'] = 'Vous avez de la marge pour progresser. Consultez les recommandations personnalisées.';
$string['insight_needs_boost'] = 'Il est temps de donner un coup de fouet à vos études! Commencez par les recommandations ci-dessus.';
$string['improvement_suggestion_login'] = 'Conseil: Connectez-vous plus régulièrement pour rester à jour avec vos cours.';
$string['improvement_suggestion_assignment'] = 'Conseil: Concentrez-vous sur la completion de vos devoirs à temps.';
$string['improvement_suggestion_engagement'] = 'Conseil: Participez plus activement aux activités de cours.';
$string['improvement_suggestion_grade'] = 'Conseil: Demandez de l\'aide à vos enseignants ou pairs pour améliorer vos notes.';
$string['privacy_note'] = 'Toutes les comparaisons sont anonymes. Vos pairs ne peuvent pas voir vos données individuelles.';

// Goals and progress tracking.
$string['mygoals'] = 'Mes objectifs';
$string['totalgoals'] = 'Total objectifs';
$string['activegoals'] = 'Objectifs actifs';
$string['completedgoals'] = 'Objectifs completés';
$string['completionrate'] = 'Taux de completion';
$string['suggestedgoals'] = 'Objectifs suggérés';
$string['createthisgoal'] = 'Créer cet objectif';
$string['noactivegoals'] = 'Aucun objectif actif. Créez-en un pour suivre votre progression!';
$string['daysremaining'] = 'jours restants';
$string['progress'] = 'Progression';
$string['completedon'] = 'Completé le {$a}';
$string['createcustomgoal'] = 'Créer un objectif personnalisé';
$string['customgoal_desc'] = 'Définissez vos propres objectifs pour rester motivé et suivre votre progression.';
$string['goaltitle'] = 'Titre de l\'objectif';
$string['goaldescription'] = 'Description';
$string['targetvalue'] = 'Valeur cible';
$string['deadline'] = 'Échéance';
$string['creategoal'] = 'Créer l\'objectif';
$string['goalcreated'] = 'Objectif créé avec succès!';
$string['goal_complete_assignments'] = 'Rattraper les devoirs en retard';
$string['goal_complete_assignments_desc'] = 'Completez tous vos devoirs manquants pour améliorer votre performance.';
$string['goal_increase_logins'] = 'Augmenter la fréquence de connexion';
$string['goal_increase_logins_desc'] = 'Connectez-vous plus régulièrement pour rester engagé dans vos cours.';
$string['goal_completed'] = 'Objectif completé: {$a}';

// Risk explanations.
$string['riskexplanation_faible'] = 'Vous êtes sur la bonne voie! Continuez ainsi.';
$string['riskexplanation_moyen'] = 'Attention à maintenir votre engagement.';
$string['riskexplanation_élevé'] = 'Besoin d\'amélioration. Consultez les recommandations.';
$string['riskexplanation_critique'] = 'Action immédiate requise. Contactez votre superviseur.';

// Tips for students.
$string['tip1'] = '💡 Conseil: Connectez-vous quotidiennement pendant 15 minutes pour rester à jour.';
$string['tip2'] = '📚 Astuce: Créez un planning d\'étude hebdomadaire et respectez-le.';
$string['tip3'] = '🎯 Motivation: Chaque petit progrès compte. Célébrez vos victoires!';
$string['tip4'] = '🤝 Conseil: N\'hésitez pas à demander de l\'aide à vos camarades ou enseignants.';
$string['tip5'] = '⏰ Rappel: Gérez votre temps efficacement en priorisant les tâches importantes.';


// Business Intelligence & Advanced Analytics (Phase 10).
$string['bidashboard'] = 'Tableau de bord BI';
$string['institutionaloverview'] = 'Vue d\'ensemble institutionnelle';
$string['totalstudents'] = 'Total étudiants';
$string['needsintervention'] = 'Besoin d\'intervention';
$string['successrate'] = 'Taux de réussite';
$string['avgresponsetime'] = 'Temps de réponse moyen';
$string['studentsimproved'] = 'étudiants améliorés';
$string['hoursaverage'] = 'heures en moyenne';
$string['riskdistribution'] = 'Distribution du risque';
$string['trendsandcharts'] = 'Tendances et graphiques';
$string['dailyinterventions'] = 'Interventions quotidiennes';
$string['successratetrend'] = 'Tendance taux de réussite';
$string['retentionanalytics'] = 'Analytiques de rétention';
$string['retentionrate'] = 'Taux de rétention';
$string['activestudents'] = 'étudiants actifs';
$string['atriskdropout'] = 'Risque d\'abandon';
$string['dropoutprediction'] = 'Prédiction d\'abandon';
$string['highriskinactive'] = 'Haut risque inactif';
$string['retentiontrend'] = 'Tendance de rétention';
$string['supervisorperformance'] = 'Performance des superviseurs';
$string['assignedstudents'] = 'Étudiants assignés';
$string['avgresponse'] = 'Réponse moyenne';
$string['nosupervisordata'] = 'Aucune donnée de superviseur';
$string['cohortanalysis'] = 'Analyse de cohortes';
$string['cohort'] = 'Cohorte';
$string['avgriskscore'] = 'Score de risque moyen';
$string['nocohortdata'] = 'Aucune donnée de cohorte';
$string['exportexecutivesummary'] = 'Exporter le résumé exécutif';
$string['backtobidashboard'] = 'Retour au tableau de bord BI';

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
$string['critical'] = 'Critique';
$string['high'] = 'Élevé';
$string['medium'] = 'Moyen';
$string['low'] = 'Faible';

// Students at risk page.
$string['viewstudents'] = 'Voir les étudiants';
$string['currentfilter'] = 'Filtre actuel';
$string['clearfilter'] = 'Effacer le filtre';
$string['showingatrisk'] = 'Affichage des étudiants à risque (MOYEN et plus)';
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
$string['refreshtrackinginfo'] = 'Cette opération va recalculer les niveaux de risque, les jours d\'inactivité et les devoirs manquants pour tous les étudiants actifs. Cela peut prendre plusieurs minutes selon le nombre d\'étudiants.';
$string['refreshtrackingsuccess'] = 'Analyse terminée : {$a->updated}/{$a->total} étudiants mis à jour ({$a->failed} échecs). {$a->courses} suivis de cours actualisés.';
