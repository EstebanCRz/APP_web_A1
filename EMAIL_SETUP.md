# 📧 Configuration du serveur email @amigo.fr

## 1. Configuration chez votre hébergeur

### Option A : Hébergeur web classique (OVH, O2Switch, etc.)
1. Accédez à votre panneau de contrôle (cPanel, Plesk, etc.)
2. Créez l'adresse email : **noreply@amigo.fr**
3. Notez les paramètres SMTP :
   - Serveur SMTP : `smtp.amigo.fr` ou `mail.amigo.fr`
   - Port : 587 (TLS) ou 465 (SSL)
   - Nom d'utilisateur : noreply@amigo.fr
   - Mot de passe : celui que vous avez défini

### Option B : Service externe (recommandé pour la production)

#### **SendGrid** (100 emails/jour gratuit)
```
SMTP_HOST: smtp.sendgrid.net
SMTP_PORT: 587
SMTP_USER: apikey
SMTP_PASS: votre_clé_API_sendgrid
```

#### **Mailgun** (5000 emails/mois gratuit)
```
SMTP_HOST: smtp.mailgun.org
SMTP_PORT: 587
SMTP_USER: postmaster@mg.amigo.fr
SMTP_PASS: votre_clé_API_mailgun
```

#### **Amazon SES** (très bon marché)
```
SMTP_HOST: email-smtp.eu-west-1.amazonaws.com
SMTP_PORT: 587
SMTP_USER: votre_SMTP_username
SMTP_PASS: votre_SMTP_password
```

## 2. Configuration DNS (OBLIGATOIRE)

### Enregistrements DNS à ajouter chez votre registrar de domaine :

#### **SPF (Sender Policy Framework)**
Type : TXT  
Nom : @  
Valeur : `v=spf1 mx a ip4:VOTRE_IP_SERVEUR ~all`

Exemple avec SendGrid :
```
v=spf1 include:sendgrid.net ~all
```

#### **DKIM (DomainKeys Identified Mail)**
Demandez la clé DKIM à votre hébergeur ou service email.

Type : TXT  
Nom : default._domainkey  
Valeur : (fournie par votre service email)

#### **DMARC (Domain-based Message Authentication)**
Type : TXT  
Nom : _dmarc  
Valeur : `v=DMARC1; p=none; rua=mailto:postmaster@amigo.fr`

#### **MX (Mail Exchange)**
Type : MX  
Nom : @  
Valeur : `mail.amigo.fr` (priorité 10)

## 3. Mise à jour de config.php

Dans `includes/config.php`, remplacez :

```php
define('SMTP_HOST', 'smtp.amigo.fr');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@amigo.fr');
define('SMTP_PASS', 'votre_mot_de_passe_réel');
```

## 4. Installation de PHPMailer

```bash
cd c:\Users\nocso\Documents\GitHub\APP_web_A1
composer require phpmailer/phpmailer
```

## 5. Test d'envoi

Créez `test-email.php` à la racine :

```php
<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

// Test d'envoi
$result = sendEmail(
    'votre-email-test@gmail.com',
    'Test email AmiGo',
    '<h1>Test</h1><p>Ceci est un email de test depuis AmiGo.</p>',
    true
);

if ($result) {
    echo "✅ Email envoyé avec succès !";
} else {
    echo "❌ Erreur lors de l'envoi";
}
```

Exécutez : `php test-email.php`

## 6. Intégration dans l'application

### Lors de l'inscription (register.php)
```php
require_once '../includes/mailer.php';

// Après l'insertion en base de données
sendWelcomeEmail($email, $firstName);
```

### Réinitialisation de mot de passe (forgot-password.php)
```php
require_once '../includes/mailer.php';

// Générer un token
$token = bin2hex(random_bytes(32));

// Envoyer l'email
sendPasswordResetEmail($email, $username, $token);
```

### Notification d'événement
```php
require_once '../includes/mailer.php';

sendEventNotification($userEmail, $userName, $eventTitle, $eventDate);
```

## 7. Vérification DNS

Utilisez ces outils pour vérifier votre configuration :
- https://mxtoolbox.com/spf.aspx
- https://mxtoolbox.com/dkim.aspx
- https://dmarcian.com/dmarc-inspector/

## 8. Conseils de sécurité

1. **Ne jamais commiter** les mots de passe dans Git
2. Utilisez un fichier `.env` pour les secrets
3. Limitez le taux d'envoi (anti-spam)
4. Utilisez HTTPS pour votre site
5. Validez toujours les adresses email

## 9. Délivrabilité

Pour améliorer la délivrabilité :
- ✅ Configurez SPF, DKIM, DMARC
- ✅ Utilisez un domaine vérifié
- ✅ Évitez les mots spam (gratuit, promotion, etc.)
- ✅ Incluez un lien de désinscription
- ✅ Utilisez du HTML bien formaté
- ✅ Testez avec https://mail-tester.com/

## 10. Alternatives gratuites pour le développement

### Mailtrap (recommandé pour tests)
Intercepte tous les emails pour les tester sans les envoyer réellement :
```php
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'votre_username_mailtrap');
define('SMTP_PASS', 'votre_password_mailtrap');
```
Inscription : https://mailtrap.io/

## Support

Pour toute question :
- SendGrid : https://sendgrid.com/docs/
- Mailgun : https://documentation.mailgun.com/
- PHPMailer : https://github.com/PHPMailer/PHPMailer
