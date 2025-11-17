# Student Monitor - Plugin Moodle

**Version:** 1.3.0
**Compatible Moodle:** 4.0+
**PHP:** 8.0+
**Licence:** GNU GPL v3

---

## 📋 Description

Student Monitor est un plugin Moodle développé pour l'**UNCHK (Université Numérique Cheikh Hamidou Kane)** au Sénégal. Il améliore la rétention des étudiants en Licence 1 grâce à un système automatisé de suivi et de notifications.

### Fonctionnalités principales

✅ **4 types de notifications automatiques :**
1. Détection d'inactivité (seuils configurables : 72h, 7j, 14j)
2. Notification de nouveau contenu pédagogique
3. Rappels de devoirs à échéance (J-7, J-3, J-1, H-6)
4. Annonces institutionnelles depuis un forum dédié

✅ **Alertes manuelles** - Les superviseurs peuvent créer des alertes personnalisées

✅ **Dashboard interactif** - Visualisation des étudiants à risque et statistiques

✅ **Multi-canaux** - Email, Notifications Moodle, SMS, WhatsApp Business

✅ **Évaluation des risques** - Calcul automatique du niveau de risque (FAIBLE, MOYEN, ÉLEVÉ, CRITIQUE)

✅ **RGPD compliant** - Conformité avec le Privacy API de Moodle

---

## 🚀 Installation

### Méthode 1 : Via Git

```bash
cd /path/to/moodle/local/
git clone https://github.com/unchk/student_monitor.git student_monitor
cd student_monitor
```

### Méthode 2 : Manuel

1. Téléchargez l'archive du plugin
2. Extrayez dans `moodle/local/student_monitor`
3. Accédez à **Administration du site > Notifications**
4. Cliquez sur "Mettre à jour la base de données"

---

## ⚙️ Configuration

### Configuration de base

1. Allez dans **Administration du site > Plugins > Local plugins > Student Monitor**

2. **Paramètres généraux :**
   - Activer le plugin
   - Configurer les seuils d'inactivité (3j, 7j, 14j par défaut)

3. **Rappels de devoirs :**
   - Activer les rappels automatiques
   - Définir les jours de rappel (ex: 7,3,1)

4. **Forum institutionnel :**
   - Indiquer l'ID du forum pour les annonces

5. **Canaux de notification :**
   - Email : ✅ Activé par défaut
   - Notifications Moodle : ✅ Activé par défaut
   - SMS : Configuration API requise
   - WhatsApp : Configuration API requise

### Configuration SMS (optionnelle)

Pour activer les SMS, configurez :
- **URL de l'API SMS** : Endpoint de votre fournisseur (Orange, Twilio, etc.)
- **Clé API SMS** : Clé d'authentification

### Configuration WhatsApp (optionnelle)

Pour activer WhatsApp Business :
- **ID du numéro WhatsApp** : Phone Number ID depuis Meta Business
- **Token d'accès** : Access Token de l'API WhatsApp

### Configuration Email de support

- **Email du support** : support@unchk.edu.sn
- **Téléphone du support** : +221 XX XXX XX XX

---

## 📊 Utilisation

### Pour les superviseurs/managers

#### Accéder au Dashboard

1. Allez dans **Navigation > Student Monitor**
2. Visualisez les KPI :
   - Étudiants à risque
   - Notifications envoyées
   - Interventions nécessaires
   - Taux de lecture

#### Créer une alerte manuelle

1. Dashboard > **Créer une alerte**
2. Choisir le type (Examen, Devoir, Annonce, Événement)
3. Remplir les détails (titre, date, description)
4. Sélectionner les destinataires
5. Choisir les canaux de diffusion
6. Activer les rappels automatiques (optionnel)
7. Envoyer

#### Suivi des étudiants

Le tableau de bord affiche :
- **Niveau de risque** : Badge coloré par étudiant
- **Jours d'inactivité** : Depuis la dernière connexion
- **Devoirs manquants** : Nombre de devoirs non rendus
- **Notifications envoyées** : Historique

#### Actions disponibles

- **Assigner à un superviseur** : Affecter un étudiant à un conseiller
- **Ajouter des notes** : Documenter les interventions
- **Voir l'historique** : Consulter toutes les notifications envoyées
- **Exporter CSV** : Export des données pour analyse
- **Actions en masse** : Effectuer des actions sur plusieurs étudiants simultanément

#### Filtres avancés (v1.3.0)

Le dashboard inclut maintenant des filtres avancés pour affiner la liste des étudiants :

1. **Recherche** - Chercher par nom ou email
2. **Niveau de risque** - Filtrer par FAIBLE, MOYEN, ÉLEVÉ, CRITIQUE
3. **Jours d'inactivité** - Seuil minimum de jours
4. **Devoirs manquants** - Nombre minimum de devoirs
5. **Assignation** - Filtrer assignés/non assignés

#### Rapports avancés (v1.3.0)

Accédez à la page **Rapports avancés** pour visualiser :

1. **Graphiques Chart.js** :
   - Répartition des risques (donut chart)
   - Tendances des notifications (line chart)
   - Types de notifications (bar chart)
   - Interventions par risque (horizontal bar)

2. **KPI visuels** :
   - Total étudiants
   - Notifications (30 derniers jours)
   - Étudiants à risque
   - Taux de lecture

3. **Export de données** :
   - Export CSV étudiants
   - Export CSV notifications

#### Actions en masse (v1.3.0)

La page **Actions en masse** permet de :

1. **Assigner à un superviseur** - Affecter plusieurs étudiants à un superviseur
2. **Retirer l'assignation** - Désassigner plusieurs étudiants
3. **Ajouter une note** - Ajouter la même note à plusieurs étudiants
4. **Envoyer une notification** - Notifier plusieurs étudiants simultanément

Avec confirmation avant exécution et rapport de succès/échec.

#### Éditeur de templates (v1.3.0)

Personnalisez les templates de notification :

1. Dashboard > **Éditeur de templates**
2. Modifiez le sujet et le corps des messages
3. Utilisez les placeholders pour personnalisation
4. Réinitialisez aux valeurs par défaut si nécessaire

Templates disponibles :
- Inactivité niveau 1, 2, 3
- Nouveau contenu
- Rappel de devoir
- Annonce institutionnelle

### Pour les enseignants

Les enseignants peuvent :
- Créer des alertes pour leurs cours
- Voir les étudiants à risque dans leurs cours
- Consulter les statistiques de leur cours
- **Configurer Student Monitor pour leur cours** : Accéder aux paramètres spécifiques via le menu du cours

#### Configurer les paramètres du cours

1. Dans votre cours > **Plus** > **Student Monitor Settings**
2. Paramètres disponibles :
   - Activer/désactiver Student Monitor pour ce cours
   - Choisir les types d'activités à surveiller (devoirs, tests, forums, etc.)
   - Configurer les rappels de devoirs personnalisés
   - Définir un seuil d'inactivité personnalisé
   - Assigner un superviseur par défaut
   - Activer les résumés périodiques (quotidien, hebdomadaire, mensuel)

### Pour les étudiants

Les étudiants reçoivent automatiquement :
- Notifications d'inactivité si absence prolongée
- Alertes de nouveau contenu dans leurs cours
- Rappels de devoirs à rendre
- Annonces institutionnelles importantes

#### Gérer ses préférences de notification

Les étudiants peuvent personnaliser leurs canaux de réception :

1. **Navigation** > **Student Monitor** > **Préférences**
2. Choisir les canaux souhaités :
   - ✉️ Email
   - 🔔 Notifications Moodle
   - 📱 SMS (si activé)
   - 💬 WhatsApp (si activé)
3. Consulter l'historique de leurs notifications

---

## 🔧 Tâches planifiées (CRON)

Le plugin utilise 6 tâches planifiées :

| Tâche | Fréquence | Description |
|-------|-----------|-------------|
| **check_inactivity** | Toutes les 6h | Détecte les étudiants inactifs |
| **check_assignments_due** | Quotidien (1h) | Vérifie les devoirs à échéance |
| **send_scheduled_notifications** | Toutes les 15min | Envoie les notifications en attente |
| **update_student_tracking** | Quotidien (2h30) | Met à jour les données de suivi |
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

Le plugin crée 5 tables :

1. **local_sm_notifications** - Stocke toutes les notifications
2. **local_sm_student_tracking** - Données de suivi et risques
3. **local_sm_config** - Configuration par cours
4. **local_sm_logs** - Logs des actions
5. **local_sm_templates** - Modèles de messages

---

## 🎨 Personnalisation des templates

Les templates par défaut sont en français. Pour les modifier :

1. Allez dans la base de données : `mdl_local_sm_templates`
2. Modifiez le champ `body` et `subject`
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
- `{riskLevel}` - Niveau de risque

**Devoirs :**
- `{assignmentname}` - Nom du devoir
- `{duedate}` - Date limite
- `{submissionlink}` - Lien de soumission

**Nouveau contenu :**
- `{coursename}` - Nom du cours
- `{modulename}` - Nom du module
- `{modulelink}` - Lien vers le module

---

## 🔒 Sécurité et RGPD

Le plugin respecte la réglementation RGPD :

- ✅ **Privacy API** implémenté
- ✅ Export des données personnelles
- ✅ Suppression des données utilisateur
- ✅ Logs d'actions traçables
- ✅ Nettoyage automatique des anciennes données

### Permissions (capabilities)

- `local/student_monitor:viewdashboard` - Voir le dashboard
- `local/student_monitor:managesettings` - Gérer les paramètres
- `local/student_monitor:sendmanual` - Envoyer des alertes manuelles
- `local/student_monitor:viewreports` - Voir les rapports
- `local/student_monitor:viewstudentdata` - Voir les données étudiants
- `local/student_monitor:intervene` - Intervenir (assigner, notes)
- `local/student_monitor:exportdata` - Exporter les données

---

## 🧪 Tests

Le plugin inclut des tests unitaires complets pour les fonctionnalités principales :

### Tests disponibles

- **notification_manager_test.php** - Tests du gestionnaire de notifications
  - Création de notifications
  - Remplacement des placeholders
  - Vérification des notifications récentes
  - Mise à jour des statuts

- **student_tracker_test.php** - Tests du suivi étudiant
  - Mise à jour du suivi
  - Calcul des niveaux de risque (4 niveaux)
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

- **Email** : support-dev@unchk.edu.sn
- **Documentation** : https://docs.unchk.edu.sn/student-monitor
- **Issues** : https://github.com/unchk/student_monitor/issues

---

## 📝 Changelog

### Version 1.3.0 (2025-11-17)

**Phase 4 - Visualisation & Reporting avancé**

- ✅ Intégration Chart.js pour graphiques interactifs
- ✅ Filtres avancés dans la liste des étudiants
- ✅ Actions en masse (bulk actions)
- ✅ Page de rapports avancés avec 4 graphiques
- ✅ Éditeur de templates de notification
- ✅ 60+ nouvelles chaînes de langue (FR/EN)

**Nouvelles fonctionnalités :**
- **Graphiques Chart.js** : Répartition des risques, tendances des notifications, types de notifications, interventions par risque
- **Filtres avancés** : Recherche par nom/email, filtres par risque, inactivité, devoirs, assignation
- **Actions en masse** : Assigner/désassigner, ajouter notes, notifier plusieurs étudiants simultanément
- **Rapports avancés** : Page dédiée avec KPI visuels et graphiques interactifs
- **Éditeur de templates** : Personnaliser les messages avec réinitialisation aux valeurs par défaut

### Version 1.2.0 (2025-11-17)

**Phase 3 - Configuration & Testing**

- ✅ Paramètres spécifiques par cours (course_settings.php)
- ✅ Préférences de notification pour les étudiants (preferences.php)
- ✅ Templates Mustache réutilisables (kpi_card, student_row)
- ✅ Tests unitaires (notification_manager, student_tracker)
- ✅ 50+ nouvelles chaînes de langue (FR/EN)
- ✅ Améliorations de la personnalisation

**Nouvelles fonctionnalités :**
- Configuration granulaire par cours (activation, types d'activités, seuils personnalisés)
- Résumés périodiques pour enseignants (quotidien, hebdomadaire, mensuel)
- Préférences de canaux pour les étudiants
- Historique des notifications pour les étudiants
- Tests unitaires complets pour les managers principaux

### Version 1.1.0 (2025-11-17)

**Phase 2 - Interface & Alertes manuelles**

- ✅ Dashboard interactif avec KPI et statistiques
- ✅ Système d'alertes manuelles complet
- ✅ Historique et suivi des alertes
- ✅ Export CSV avec encodage UTF-8
- ✅ Styles CSS responsive (300+ lignes)
- ✅ Module JavaScript AMD pour interactivité
- ✅ Fournisseurs de messages Moodle

**Nouvelles fonctionnalités :**
- Interface utilisateur complète et moderne
- Création d'alertes personnalisées (examens, devoirs, événements)
- Filtrage des étudiants par niveau de risque
- Actions rapides depuis le dashboard
- Rappels automatiques (J-7, J-3, J-1)

### Version 1.0.0 (2025-11-17)

**Phase 1 - Infrastructure & Notifications automatiques**

- ✅ Implémentation initiale
- ✅ Détection d'inactivité (3 niveaux : 72h, 7j, 14j)
- ✅ Notifications de nouveau contenu
- ✅ Rappels de devoirs automatiques
- ✅ Annonces institutionnelles
- ✅ Multi-canaux (Email, Moodle, SMS, WhatsApp)
- ✅ Système d'évaluation des risques (4 niveaux)
- ✅ Conformité RGPD/Privacy API
- ✅ 6 tâches CRON planifiées
- ✅ 6 observateurs d'événements
- ✅ Base de données (5 tables)
- ✅ 8 permissions (capabilities)

---

## 👥 Contributeurs

- **UNCHK Development Team**
- Université Numérique Cheikh Hamidou Kane, Sénégal

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

**Développé avec ❤️ pour améliorer la réussite étudiante à l'UNCHK.**
