# Guide de Configuration WhatsApp Business API

## Vue d'ensemble

Student Monitor utilise l'API WhatsApp Business de Meta (Facebook) pour envoyer des notifications aux étudiants via WhatsApp.

## Prérequis

- Un compte Facebook/Meta Business Manager
- Un numéro de téléphone dédié pour WhatsApp Business
- Accès à l'API WhatsApp Business (pas WhatsApp Business App)

## Étape 1 : Créer un compte Meta Business

1. **Accédez à Meta Business Suite**
   - URL : https://business.facebook.com
   - Cliquez sur "Créer un compte"
   - Remplissez les informations de votre organisation

2. **Vérifiez votre entreprise**
   - Meta peut demander des documents de vérification
   - Préparez : registre de commerce, document d'identité, etc.

## Étape 2 : Configurer WhatsApp Business API

### 2.1 Créer une application

1. Allez sur **Meta for Developers**
   - URL : https://developers.facebook.com
   - Connectez-vous avec votre compte Business

2. **Créer une nouvelle application**
   - Cliquez sur "Mes applications" → "Créer une application"
   - Sélectionnez "Business" comme type d'application
   - Donnez un nom à votre application (ex: "Student Monitor UNCHK")

3. **Ajouter WhatsApp à votre application**
   - Dans le tableau de bord de l'application
   - Cliquez sur "Ajouter un produit"
   - Sélectionnez "WhatsApp" → "Configurer"

### 2.2 Obtenir vos identifiants

1. **Phone Number ID**
   - Dans WhatsApp → Configuration
   - Section "Numéros de téléphone"
   - Copiez le **Phone Number ID** (format : `123456789012345`)

2. **Access Token**
   - Dans WhatsApp → Démarrage rapide
   - Générez un **Access Token** permanent
   - ⚠️ **Important** : Conservez ce token en sécurité !

### 2.3 Ajouter un numéro de téléphone

Vous avez 2 options :

**Option A : Numéro de test (gratuit, limité)**
- Meta fournit un numéro de test
- Limité à 5 destinataires
- Idéal pour les tests

**Option B : Numéro Business (production)**
- Vous devez fournir un numéro dédié
- Vérification par SMS/appel requis
- Le numéro ne peut pas être utilisé sur WhatsApp standard
- Format recommandé : SIM spéciale ou numéro virtuel

## Étape 3 : Configuration dans Moodle

### 3.1 Accéder aux paramètres

```
Administration du site
  → Plugins
    → Plugins locaux
      → Student Monitor
```

### 3.2 Paramètres WhatsApp

1. **Activer WhatsApp**
   - Cochez la case "Activer WhatsApp"

2. **ID du numéro WhatsApp**
   ```
   Champ : whatsapp_phone_id
   Valeur : Votre Phone Number ID (ex: 123456789012345)
   ```

3. **Token d'accès WhatsApp**
   ```
   Champ : whatsapp_token
   Valeur : Votre Access Token (ex: EAAxxxxxxxxxxxxxxxxxx)
   ```

4. Cliquez sur **"Enregistrer les modifications"**

## Étape 4 : Configuration des utilisateurs

### 4.1 Format des numéros de téléphone

Les numéros doivent être au **format international sans le symbole +** :

```
Sénégal : 221771234567
France : 33612345678
```

### 4.2 Remplir les numéros dans Moodle

Les utilisateurs doivent avoir un numéro dans leur profil :
- Champ `phone1` (téléphone 1)
- OU champ `phone2` (téléphone 2)

**Pour mettre à jour en masse :**
1. Administration du site → Utilisateurs → Comptes → Envoi de fichiers
2. Préparez un CSV avec : `username,phone1`
3. Importez le fichier

## Étape 5 : Templates de messages (Important !)

### Pourquoi des templates ?

WhatsApp Business API impose l'utilisation de **templates pré-approuvés** pour :
- Les messages initiés par l'entreprise (pas en réponse)
- Les messages après 24h sans interaction

### Créer un template

1. Dans Meta Business Manager → WhatsApp → Templates de messages
2. Créez un nouveau template
3. Exemple de template :

```
Nom : student_alert_notification
Catégorie : TRANSACTIONAL
Langue : Français

Corps du message :
Bonjour {{1}},

{{2}}

Date : {{3}}
Lieu : {{4}}

Cordialement,
L'équipe pédagogique
```

4. Soumettez pour approbation (peut prendre 24-48h)

### Utiliser les templates dans le code

Le système utilise actuellement l'envoi de **messages texte simples** (méthode `send_whatsapp()`).

Pour les **messages template**, utilisez `send_whatsapp_template()` :

```php
$channel_manager->send_whatsapp_template(
    $phone,
    'student_alert_notification',
    [
        $student_name,      // {{1}}
        $message,           // {{2}}
        $event_date,        // {{3}}
        $location           // {{4}}
    ]
);
```

## Étape 6 : Test de l'envoi

### 6.1 Test depuis l'interface Moodle

1. Accédez à **Student Monitor → Créer une alerte**
2. Remplissez le formulaire
3. Cochez **"WhatsApp"** dans les canaux
4. Sélectionnez un destinataire de test (vous-même)
5. Envoyez l'alerte

### 6.2 Vérifier les logs

Les envois WhatsApp sont enregistrés dans :
```
Table : local_sm_logs
Action : whatsapp_sent
```

Consultez les logs pour voir :
- Code HTTP (200 = succès)
- Réponse de l'API
- Erreurs éventuelles

## Étape 7 : Limitations et Quotas

### Limites de Meta

1. **Numéro de test**
   - 5 destinataires maximum
   - 1000 messages/24h

2. **Numéro Business (nouveau)**
   - 250 conversations/24h (débloque progressivement)
   - Augmente avec l'utilisation

3. **Numéro vérifié**
   - Limite augmentée en fonction de la qualité
   - Jusqu'à plusieurs millions de messages/jour

### Coûts

- **Conversation initiée par l'entreprise** : ~0.03-0.10 USD selon le pays
- **Conversation initiée par l'utilisateur** : Gratuit (24h de fenêtre)
- Tarification à la conversation (pas au message)

## Dépannage

### Erreur : "Invalid phone number"
- Vérifiez le format (sans +, sans espaces)
- Exemple correct : `221771234567`

### Erreur : "Recipient phone number not engaged"
- Le destinataire doit d'abord accepter de recevoir des messages
- Utilisez un template pré-approuvé pour le premier message

### Erreur : "Access token invalid"
- Regénérez un token permanent
- Vérifiez que vous n'avez pas copié d'espaces

### Messages non reçus
- Vérifiez que le numéro est enregistré sur WhatsApp
- Consultez les logs dans `local_sm_logs`
- Vérifiez le statut dans Meta Business Manager

## Ressources officielles

- **Documentation WhatsApp Business API** : https://developers.facebook.com/docs/whatsapp
- **Meta Business Help Center** : https://business.facebook.com/business/help
- **Status de l'API** : https://status.fb.com/

## Support

Pour toute question :
- Documentation locale : `/local/student_monitor/docs/`
- Support UNCHK : support@unchk.edu.sn

---

**Dernière mise à jour** : 2025-01-17
**Version** : 1.0
