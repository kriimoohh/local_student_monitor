# Student Monitor - Plugin Moodle

**Version:** 3.2.0
**Compatible Moodle:** 4.0+
**PHP:** 8.0+
**Licence:** GNU GPL v3
**Auteur:** kriimoohh

---

## 📋 Description

Student Monitor est un plugin Moodle complet développé initialement pour l'**UNCHK (Université Numérique Cheikh Hamidou KANE)** au Sénégal.

> **🌍 Plugin universel** : Bien que conçu pour l'UNCHK, ce plugin est entièrement adaptable et utilisable par **toute autre institution éducative** (universités, écoles, centres de formation) souhaitant améliorer le suivi et la rétention de ses étudiants.

Il améliore la rétention des étudiants grâce à un système automatisé de suivi, d'analyse et de notifications multicanaux.

### Fonctionnalités principales

✅ **Système de notifications automatiques intelligent :**
1. Détection d'inactivité multi-niveaux (3, 7, 14 jours - configurables)
2. Notifications de nouveau contenu pédagogique
3. Rappels automatiques de devoirs (J-7, J-3, J-1, H-6)
4. Annonces institutionnelles depuis un forum dédié
5. **Configuration graphique** - Activation/désactivation et paramétrage sans code

✅ **Gestion avancée des alertes manuelles :**
- Création d'alertes personnalisées par type (examen, devoir, événement, annonce)
- **Sélection ciblée par niveau d'inactivité** (3+, 7+, 14+ jours)
- **Sélection par niveau de risque** (CRITICAL, HIGH, MEDIUM)
- Destinataires multiples (catégories, cours, groupes, sélection manuelle, import CSV)
- Rappels automatiques programmables
- Aperçu de l'alerte avant envoi (`preview_alert.php`)

✅ **Évaluation des risques (simplifiée depuis v3.0.0) :**
- 4 niveaux : **LOW, MEDIUM, HIGH, CRITICAL**
- Calcul "le critère le plus élevé l'emporte" entre :
  - Jours d'inactivité (seuils configurables : niveaux 1/2/3)
  - Activités manquantes (devoirs, quiz, activités à compléter — seuils configurables : 1/3/5 par défaut)
- Suivi de **toutes** les activités du cours (achèvement d'activité Moodle, soumissions de devoirs, tentatives de quiz), pas seulement les devoirs
- Migration automatique des anciennes valeurs françaises (FAIBLE/MOYEN/ÉLEVÉ/CRITIQUE) vers les nouvelles valeurs anglaises

✅ **Page dédiée Étudiants à Risque :**
- Vue d'ensemble avec statistiques cliquables par niveau de risque
- Filtrage avancé et tri multi-critères
- Pagination flexible (25, 50, 100, 200 par page)
- Indicateurs visuels (icônes de criticité, badges colorés)
- Actions rapides (profil, notification)
- Export CSV avec filtres appliqués

✅ **Dashboard interactif et complet :**
- KPI en temps réel (étudiants à risque, notifications, interventions)
- Widget de configuration des alertes automatiques (administrateurs)
- Accès rapide à toutes les fonctionnalités via un menu structuré
- Alertes critiques en temps réel

✅ **Multi-canaux intelligent :**
- Email (natif Moodle)
- Notifications Moodle
- SMS (avec suivi des coûts et budget mensuel)
- WhatsApp Business (templates pré-approuvés)
- Configuration par canal pour les alertes automatiques

✅ **Rapports avancés :**
- Graphiques Chart.js (répartition des risques, tendances, types de notifications, interventions)
- **Rapports d'efficacité** (`effectiveness.php`) : impact des interventions et notifications
- **Planificateur de rapports** (PDF/CSV/HTML, envoi par email programmé)
- Export PDF (liste étudiants, rapport détaillé, historique des notifications)
- Rapport hebdomadaire automatique (`weekly_report.php`)

✅ **Préférences de notification :**
- **Préférences de notification** (`preferences.php`) : choix des canaux et historique, accessible à tous les utilisateurs (conformité RGPD)

✅ **Suivi des interventions et tâches :**
- Suivi des interventions auprès des étudiants (assignation, notes, historique)
- Gestion de tâches de suivi (`tasks.php`)
- Actions en masse (`bulk_actions.php`)

✅ **API mobile (Web Services) :**
- `local_student_monitor_get_student_stats` - statistiques de suivi d'un étudiant
- `local_student_monitor_search_users` - recherche d'utilisateurs pour les alertes

✅ **RGPD et sécurité :**
- Conformité Privacy API Moodle
- Export/suppression des données personnelles
- Logs d'actions traçables
- Permissions granulaires (8 capabilities)

---

## 🚀 Installation

### Methode 1 : Via les Releases GitHub (Recommandee)

1. Allez sur la page [Releases](../../releases) du projet
2. Telechargez le fichier `student_monitor.zip` de la derniere version
3. Extrayez le ZIP dans le dossier `local/` de votre installation Moodle
   - Le dossier extrait s'appelle deja `student_monitor` (pret a l'emploi)
4. Accedez a **Administration du site > Notifications**
5. Cliquez sur "Mettre a jour la base de donnees"

### Methode 2 : Via Git

```bash
cd /path/to/moodle/local/
git clone https://github.com/kriimoohh/local_student_monitor.git student_monitor
```

> **Important :** Le dossier doit s'appeler `student_monitor` (sans le prefixe `local_`)

### Methode 3 : Telechargement manuel depuis GitHub

1. Telechargez le ZIP du depot depuis GitHub
2. Extrayez l'archive
3. **Renommez** le dossier en `student_monitor` (supprimez le prefixe `local_` et le suffixe de branche)
4. Placez le dossier dans `moodle/local/student_monitor`
5. Accedez a **Administration du site > Notifications**
6. Cliquez sur "Mettre a jour la base de donnees"

---

## ⚙️ Configuration

### Configuration de base (Administration du site > Plugins > Local plugins > Student Monitor)

1. **Général** :
   - Activer le plugin
   - Nom de l'institution (utilisé dans les templates de message)

2. **Seuils d'inactivité (en jours)** :
   - Niveau 1 (par défaut : 3)
   - Niveau 2 (par défaut : 7)
   - Niveau 3 (par défaut : 14)

3. **Seuils d'activités manquantes** :
   - Niveau 1 (par défaut : 1)
   - Niveau 2 (par défaut : 3)
   - Niveau 3 (par défaut : 5)

4. **Rappels de devoirs** :
   - Activer les rappels automatiques
   - Définir les jours de rappel (ex: 7,3,1)

5. **Forum institutionnel** :
   - Indiquer l'ID du forum pour les annonces

6. **Canaux de notification** :
   - Email : ✅ Activé par défaut
   - Notifications Moodle : ✅ Activé par défaut
   - SMS : Configuration API requise
   - WhatsApp : Configuration API requise

7. **Email/téléphone de support** : utilisés dans les placeholders `{supportemail}` / `{supportphone}`

8. **Expéditeur des emails** :
   - Adresse email d'expédition (optionnelle, sinon l'adresse no-reply de Moodle est utilisée)
   - Nom de l'expéditeur (optionnel, appliqué seulement si une adresse est définie)

### Configuration SMS (optionnelle)

Pour activer les SMS, configurez :
- **URL de l'API SMS** : Endpoint de votre fournisseur (Orange, Twilio, etc.)
- **Clé API SMS** : Clé d'authentification

Le coût des SMS est suivi automatiquement (table `local_sm_sms_costs`), avec un budget mensuel configurable et un blocage automatique en cas de dépassement.

### Configuration WhatsApp (optionnelle)

Pour activer WhatsApp Business :
- **ID du numéro WhatsApp** : Phone Number ID depuis Meta Business
- **Token d'accès** : Access Token de l'API WhatsApp

---

## 📊 Utilisation

Le plugin ajoute deux entrées principales à la navigation Moodle :

### 1. Menu "Student Monitor" (superviseurs/administrateurs)

Visible pour les utilisateurs disposant de la capability `viewdashboard`. Organisé en sections :

#### 📊 Tableaux de bord
- **Dashboard** (`dashboard.php`) - KPI en temps réel, widgets de configuration, accès rapides
- **Rapport hebdomadaire** (`weekly_report.php`) - synthèse hebdomadaire automatique

#### 👥 Gestion des étudiants
- **Étudiants à risque** (`students_at_risk.php`) - vue filtrable/triable par niveau de risque
- **Actions en masse** (`bulk_actions.php`) - assigner/désassigner un superviseur, ajouter une note, notifier plusieurs étudiants (capability `intervene`)

#### 📧 Alertes & Notifications (capability `sendmanual`)
- **Créer une alerte** (`create_alert.php`) - alertes manuelles (examen, devoir, événement, annonce) avec aperçu (`preview_alert.php`)
- **Voir les alertes** (`view_alerts.php`) - historique des alertes envoyées
- **Configurer les alertes automatiques** (`configure_automatic_alerts.php`, capability `managesettings`) - activation, seuils, canaux

#### 📈 Rapports & Analytics (capability `viewreports`)
- **Rapports avancés** (`reports.php`) - graphiques Chart.js, KPI, exports CSV
- **Rapports d'efficacité** (`effectiveness.php`) - mesure de l'impact des interventions/notifications
- **Planification de rapports** (`report_schedules.php`) - rapports automatiques périodiques (PDF/CSV/HTML)

#### 💬 Communication (capability `viewreports`)
- **Statistiques de communication** (`communication_stats.php`) - suivi SMS/WhatsApp, coûts, budgets
- **Éditeur de templates** (`template_editor.php`, capability `managetemplates`) - personnalisation des messages

#### ⚙️ Gestion (capability `intervene`)
- **Gestion des tâches** (`tasks.php`)

### 2. Préférences de notification (tous les utilisateurs authentifiés)

- **Préférences de notification** (`preferences.php`) - choix des canaux et historique, accessible directement depuis la navigation principale (conformité RGPD)

### 3. Paramètres par cours

Dans un cours > **Plus** > **Student Monitor Settings** (`course_settings.php`, capability `managesettings`) :
- Activer/désactiver Student Monitor pour ce cours
- Choisir les types d'activités à surveiller
- Configurer les rappels de devoirs et le seuil d'inactivité personnalisés
- Assigner un superviseur par défaut
- Activer des résumés périodiques (quotidien, hebdomadaire, mensuel)

---

## 🔧 Tâches planifiées (CRON)

Le plugin utilise 6 tâches planifiées :

| Tâche | Fréquence | Description |
|-------|-----------|-------------|
| **check_inactivity** | Toutes les 6h | Détecte les étudiants inactifs et met à jour leur niveau de risque |
| **check_assignments_due** | Quotidien (1h) | Vérifie les devoirs/activités à échéance et envoie les rappels |
| **send_scheduled_notifications** | Toutes les 15min | Envoie les notifications en attente |
| **update_student_tracking** | Quotidien (2h30) | Met à jour les données de suivi (activités, risques) |
| **generate_weekly_report** | Hebdo (Lundi 8h) | Génère le rapport hebdomadaire |
| **cleanup_old_logs** | Mensuel (1er à 3h) | Nettoie les anciens logs |

Pour vérifier que les tâches sont bien configurées :
```bash
php admin/cli/scheduled_task.php --list | grep student_monitor
```

Pour exécuter manuellement une tâche :
```bash
php admin/cli/scheduled_task.php --execute='\local_student_monitor\task\check_inactivity'
```

---

## 🗄️ Structure de la base de données

Le plugin crée **9 tables** :

| Table | Description |
|-------|-------------|
| `local_sm_notifications` | Historique de toutes les notifications envoyées |
| `local_sm_student_tracking` | Données de suivi (risque, inactivité, activités manquantes, superviseur, notes) |
| `local_sm_config` | Configuration par cours |
| `local_sm_logs` | Logs des actions |
| `local_sm_templates` | Modèles de messages (sujet/corps, par langue et par type) |
| `local_sm_report_schedules` | Planification des rapports automatiques |
| `local_sm_tasks` | Tâches de suivi/intervention |
| `local_sm_interventions` | Historique des interventions auprès des étudiants |
| `local_sm_sms_costs` | Suivi des coûts SMS et du budget mensuel |

---

## 🎨 Personnalisation des templates

Les templates par défaut peuvent être édités via l'**Éditeur de templates** (`template_editor.php`) ou directement en base :

1. Allez dans la table : `mdl_local_sm_templates`
2. Modifiez les champs `subject` et `body` (selon la colonne `type` et `language`)
3. Utilisez les placeholders disponibles :

### Placeholders globaux
- `{firstname}`, `{lastname}`, `{fullname}`
- `{email}`, `{username}`
- `{currentdate}`, `{institutionname}`
- `{supportemail}`, `{supportphone}`

### Placeholders spécifiques

**Inactivité :**
- `{days}` - Nombre de jours d'inactivité
- `{lastaccess}` - Date du dernier accès
- `{riskLevel}` - Niveau de risque (LOW / MEDIUM / HIGH / CRITICAL)

**Devoirs / activités :**
- `{assignmentname}` - Nom du devoir/activité
- `{duedate}` - Date limite
- `{submissionlink}` - Lien de soumission

**Nouveau contenu :**
- `{coursename}` - Nom du cours
- `{modulename}` - Nom du module
- `{modulelink}` - Lien vers le module

Types de templates disponibles : inactivité (niveau 1, 2, 3), nouveau contenu, rappel de devoir, annonce institutionnelle, alerte manuelle.

---

## 🔒 Sécurité et RGPD

Le plugin respecte la réglementation RGPD :

- ✅ **Privacy API** implémenté (`classes/privacy/provider.php`)
- ✅ Export des données personnelles
- ✅ Suppression des données utilisateur
- ✅ Logs d'actions traçables
- ✅ Nettoyage automatique des anciennes données (`cleanup_old_logs`)

### Permissions (capabilities)

- `local/student_monitor:viewdashboard` - Voir le dashboard et les tableaux de bord
- `local/student_monitor:managesettings` - Gérer les paramètres (plugin, cours, alertes automatiques)
- `local/student_monitor:sendmanual` - Envoyer des alertes manuelles et gérer les campagnes
- `local/student_monitor:viewreports` - Voir les rapports et analytics
- `local/student_monitor:viewstudentdata` - Voir les données de suivi des étudiants
- `local/student_monitor:intervene` - Intervenir (assigner, notes, actions en masse, tâches, parents)
- `local/student_monitor:exportdata` - Exporter les données
- `local/student_monitor:managetemplates` - Gérer les templates de notification

---

## 🧪 Tests

Le plugin inclut des tests unitaires pour les fonctionnalités principales :

### Tests disponibles

- **notification_manager_test.php** - Tests du gestionnaire de notifications
  - Création de notifications
  - Remplacement des placeholders
  - Vérification des notifications récentes
  - Mise à jour des statuts

- **student_tracker_test.php** - Tests du suivi étudiant
  - Mise à jour du suivi
  - Calcul des niveaux de risque (LOW/MEDIUM/HIGH/CRITICAL, basé sur `risk_level::from_criteria()`)
  - Attribution aux superviseurs
  - Ajout de notes
  - Récupération des étudiants à risque
  - Statistiques

### Exécuter les tests

```bash
# Initialiser PHPUnit (première fois uniquement)
php admin/tool/phpunit/cli/init.php

# Exécuter tous les tests du plugin
php vendor/bin/phpunit --filter local_student_monitor

# Exécuter un test spécifique
php vendor/bin/phpunit --filter notification_manager_test
php vendor/bin/phpunit --filter student_tracker_test
```

---

## 🐛 Dépannage

### Les notifications ne s'envoient pas

1. Vérifier que le plugin est activé
2. Vérifier que le CRON de Moodle fonctionne :
   ```bash
   php admin/cli/cron.php
   ```
3. Vérifier les logs : **Administration > Rapports > Logs**
4. Vérifier la table `mdl_local_sm_notifications` pour les statuts `failed`

### Les tâches planifiées ne s'exécutent pas

1. Vérifier que le CRON système est configuré :
   ```bash
   crontab -e
   */15 * * * * /usr/bin/php /path/to/moodle/admin/cli/cron.php
   ```

2. Vérifier le statut des tâches :
   ```bash
   php admin/cli/scheduled_task.php --list | grep student_monitor
   ```

### Logs et debugging

Activer le mode debug dans Moodle :
```php
// config.php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

Consulter les logs Student Monitor :
```sql
SELECT * FROM mdl_local_sm_logs ORDER BY timecreated DESC LIMIT 100;
```

---

## 📞 Support

Pour toute question ou problème :

- **Issues** : https://github.com/kriimoohh/local_student_monitor/issues

---

## 📝 Changelog (Résumé)

Voir [CHANGELOG.md](CHANGELOG.md) pour la documentation complète des versions.

### Version 3.2.0 (2026-06) - Filtres de critères, sécurité et refonte visuelle

- ✅ Page "Étudiants à risque" : filtres séparés pour le nombre minimum de jours d'inactivité et d'activités manquantes, en complément du filtre par niveau de risque
- ✅ Nouvelle colonne "Critère déclencheur" indiquant si le niveau de risque est dû à l'inactivité, aux activités manquantes, ou aux deux
- ✅ Corrections de sécurité : erreur fatale dans les préférences de notification, échappement des sorties HTML dans l'éditeur de templates et l'aperçu d'alerte (XSS)
- ✅ Refonte visuelle : nouveau style de titre de page, cartes/tableaux/badges/boutons/formulaires modernisés sur l'ensemble du plugin

### Version 3.1.0 (2026-02) - Simplification du périmètre (cahier des charges)

- ✅ Suppression des campagnes email avec tests A/B, de la gestion des parents/tuteurs et de l'espace self-service étudiant (tableau de bord, gamification, objectifs, comparaison entre pairs, recommandations IA, analyse prédictive)
- ✅ Ajout d'une adresse/nom d'expéditeur configurable pour les emails automatiques
- ✅ Correction de l'éditeur de templates (`template_editor.php`) : la réinitialisation aux valeurs par défaut était cassée pour tous les types de templates
- ✅ Migration de la base de données : suppression de 6 tables (`local_sm_campaigns`, `local_sm_campaign_recipients`, `local_sm_parents`, `local_sm_gamification`, `local_sm_achievements`, `local_sm_goals`)
- ✅ Les canaux SMS/WhatsApp et les préférences de notification (RGPD) sont conservés

### Version 3.0.x (2026-02) - Stabilisation et simplification du risque

- ✅ **Risque simplifié** : 4 niveaux en anglais (LOW, MEDIUM, HIGH, CRITICAL), calcul "le critère le plus élevé l'emporte" entre inactivité et activités manquantes
- ✅ Suivi de toutes les activités du cours (achèvement, devoirs, quiz), pas seulement les devoirs
- ✅ Migration automatique des anciennes valeurs de risque françaises
- ✅ Suppression du module Business Intelligence (`bi_dashboard.php`, `bi_analytics_engine.php`)
- ✅ Ajout de 11 tables manquantes au schéma (campagnes, gamification, objectifs, parents, tâches, interventions, coûts SMS, rapports planifiés, rapports personnalisés)
- ✅ Nombreux correctifs de fiabilité (`fullname()`, colonnes manquantes, doublons de prédictions, traductions)

### Version 2.1.0 (2025-11-19) - Configuration et ciblage des alertes

- ✅ Page de configuration des alertes automatiques (activation, seuils, canaux)
- ✅ Sélection de destinataires par niveau d'inactivité ou de risque
- ✅ Widget de statut sur le dashboard

### Version 2.0.0 (2025-11-19) - Page Étudiants à Risque

- ✅ Page dédiée avec statistiques interactives, filtrage, tri multi-critères, pagination et export CSV

### Version 1.7.0 - 1.9.0 (2025-11-17) - Engagement, BI et self-service

- ✅ Gamification (points, niveaux, séries, réalisations, classement)
- ✅ Campagnes email avec tests A/B
- ✅ API mobile (web services)
- ✅ Tableau de bord étudiant, objectifs personnels, comparaison entre pairs, recommandations IA
- ✅ Suivi de progression et historique

### Version 1.0.0 - 1.6.0 (2025-11-17) - Fondations

- ✅ Détection d'inactivité multi-niveaux et notifications automatiques (contenu, devoirs, annonces)
- ✅ Dashboard interactif, alertes manuelles, export CSV, modules AMD
- ✅ Configuration par cours, préférences étudiants, templates Mustache, tests unitaires
- ✅ Filtres avancés, actions en masse, rapports avec Chart.js, éditeur de templates
- ✅ Export PDF, suivi des coûts SMS, templates WhatsApp Business
- ✅ Conformité RGPD/Privacy API, 6 tâches CRON, observateurs d'événements, 8 permissions

---

## 👥 Contributeurs

- **kriimoohh** - Auteur principal
- **UNCHK (Université Numérique Cheikh Hamidou KANE)** - Institution d'origine, Sénégal

> Ce plugin est ouvert aux contributions de toute institution souhaitant l'améliorer.

---

## 📜 Licence

Ce plugin est sous licence **GNU GPL v3 ou ultérieure**.

```
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
```

---

## 🙏 Remerciements

Merci à la communauté Moodle pour les ressources et la documentation.

---

**Développé avec ❤️ par kriimoohh pour l'UNCHK (Université Numérique Cheikh Hamidou KANE) - Utilisable par toute institution éducative.**
