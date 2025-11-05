# Installation et Configuration - JS Industrie

Ce document explique comment installer et configurer le site web JS Industrie avec les fonctionnalités de contact.

## 📋 Prérequis

- Serveur web avec PHP 7.4 ou supérieur
- Fonction `mail()` PHP activée (pour l'envoi d'emails)
- OU accès SMTP (recommandé pour la production)

## 🚀 Installation

### 1. Upload des Fichiers

Téléversez tous les fichiers sur votre serveur web :
```
/votre-site/
  ├── index.html
  ├── blog.html
  ├── contact.php          ← Script de traitement contact
  ├── newsletter.php       ← Script de traitement newsletter
  ├── css/
  ├── js/
  ├── images/
  └── blog/
```

### 2. Configuration des Emails

#### Option A : Configuration Simple (mail() PHP)

Éditez `contact.php` et `newsletter.php` pour modifier ces constantes :

```php
// Dans contact.php et newsletter.php
define('CONTACT_EMAIL', 'votre-email@js-industrie.fr');  // Email de réception
define('SITE_NAME', 'JS Industrie');
define('FROM_EMAIL', 'noreply@js-industrie.fr');         // Email d'envoi
```

#### Option B : Configuration SMTP (Recommandé)

Pour une meilleure délivrabilité, utilisez une bibliothèque comme PHPMailer :

1. **Installer PHPMailer :**
```bash
composer require phpmailer/phpmailer
```

2. **Modifier les scripts PHP :**

Créez un fichier `config/email.php` :
```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body, $replyTo = null) {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.votreserveur.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'votre-email@js-industrie.fr';
        $mail->Password   = 'votre-mot-de-passe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur
        $mail->setFrom('noreply@js-industrie.fr', 'JS Industrie');
        $mail->addAddress($to);

        if ($replyTo) {
            $mail->addReplyTo($replyTo['email'], $replyTo['name']);
        }

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
}
```

### 3. Permissions des Fichiers

Assurez-vous que le serveur web peut écrire dans le répertoire pour `newsletter_subscribers.csv` :

```bash
chmod 644 newsletter_subscribers.csv
# ou si le fichier n'existe pas encore :
touch newsletter_subscribers.csv
chmod 644 newsletter_subscribers.csv
chown www-data:www-data newsletter_subscribers.csv
```

### 4. Configuration du Serveur Web

#### Apache (.htaccess)

Si vous n'avez pas de `.htaccess`, créez-en un :

```apache
# Sécurité
<Files "newsletter_subscribers.csv">
    Order Allow,Deny
    Deny from all
</Files>

# Redirection HTTPS (si vous avez un certificat SSL)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

#### Nginx

Ajoutez ces directives dans votre configuration :

```nginx
location ~ ^/newsletter_subscribers\.csv$ {
    deny all;
    return 404;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## 🔒 Sécurité

### Protection Anti-Spam

Les formulaires incluent déjà :
- **Honeypot** : Champ caché qui piège les bots
- **Validation côté serveur** : Vérification des données
- **Nettoyage des entrées** : Protection XSS

### Recommandations Supplémentaires

1. **Rate Limiting** : Limitez le nombre de soumissions par IP

Créez `includes/rate-limit.php` :
```php
<?php
function checkRateLimit($ip, $limit = 5, $period = 3600) {
    $file = 'temp/rate-limit-' . md5($ip) . '.txt';

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $count = $data['count'];
        $firstAttempt = $data['time'];

        if (time() - $firstAttempt < $period) {
            if ($count >= $limit) {
                return false; // Rate limit atteint
            }
            $count++;
        } else {
            $count = 1;
            $firstAttempt = time();
        }
    } else {
        $count = 1;
        $firstAttempt = time();
    }

    file_put_contents($file, json_encode(['count' => $count, 'time' => $firstAttempt]));
    return true;
}
```

Ajoutez dans `contact.php` :
```php
require_once 'includes/rate-limit.php';

$ip = $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($ip)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.']);
    exit;
}
```

2. **reCAPTCHA** : Ajoutez Google reCAPTCHA v3 pour une meilleure protection

3. **HTTPS** : Utilisez TOUJOURS HTTPS en production (Let's Encrypt gratuit)

4. **Sauvegarde** : Sauvegardez régulièrement `newsletter_subscribers.csv`

## 📧 Configuration Email Avancée

### Services d'Email Recommandés

Pour une meilleure délivrabilité, utilisez un service d'email transactionnel :

- **SendGrid** (gratuit jusqu'à 100 emails/jour)
- **Mailgun** (gratuit jusqu'à 5000 emails/mois)
- **Amazon SES** (très économique)
- **Brevo (ex-Sendinblue)** (gratuit jusqu'à 300 emails/jour)

### Exemple avec SendGrid

```php
// composer require sendgrid/sendgrid
use SendGrid\Mail\Mail;

$email = new Mail();
$email->setFrom("noreply@js-industrie.fr", "JS Industrie");
$email->setSubject($subject);
$email->addTo($to);
$email->addContent("text/html", $body);

$sendgrid = new \SendGrid('VOTRE_API_KEY');
$response = $sendgrid->send($email);
```

## 🧪 Test des Formulaires

### Test Local

1. **Installation locale avec XAMPP/MAMP :**
   - Placez le site dans `htdocs/js-industrie`
   - Accédez via `http://localhost/js-industrie`

2. **Test d'envoi :**
   - Remplissez le formulaire de contact
   - Vérifiez les logs PHP : `tail -f /var/log/apache2/error.log`

### Test de Production

```bash
# Test du script PHP directement
curl -X POST http://votre-site.fr/contact.php \
  -d "nom=Test" \
  -d "email=test@example.com" \
  -d "type_projet=Chaudronnerie" \
  -d "message=Test message"
```

## 📊 Gestion des Abonnés Newsletter

Le fichier `newsletter_subscribers.csv` contient les abonnés au format :
```csv
email@example.com,2024-11-05 10:30:45
autre@example.com,2024-11-05 11:15:20
```

### Export vers MailChimp/Sendinblue

1. Téléchargez `newsletter_subscribers.csv`
2. Importez dans votre outil de newsletter
3. Archivez l'ancien fichier
4. Créez un nouveau fichier vide

## 🐛 Dépannage

### Les emails ne sont pas envoyés

1. **Vérifiez les logs PHP :**
```bash
tail -f /var/log/apache2/error.log
```

2. **Testez la fonction mail() :**
```php
<?php
$result = mail('votre-email@example.com', 'Test', 'Message de test');
var_dump($result);
```

3. **Vérifiez la configuration PHP :**
```bash
php -i | grep sendmail
```

### Erreur 500

- Vérifiez les permissions des fichiers
- Consultez les logs d'erreur
- Vérifiez la syntaxe PHP : `php -l contact.php`

### Formulaire ne se soumet pas

- Ouvrez la console JavaScript (F12)
- Vérifiez les erreurs réseau
- Vérifiez que les chemins sont corrects (contact.php, newsletter.php)

## 📞 Support

Pour toute question :
- Email : contact@js-industrie.fr
- Vérifiez les logs : `/var/log/apache2/error.log` ou `/var/log/nginx/error.log`

---

## ✅ Checklist de Déploiement

- [ ] Fichiers uploadés sur le serveur
- [ ] Emails configurés dans contact.php et newsletter.php
- [ ] Permissions correctes sur newsletter_subscribers.csv
- [ ] HTTPS activé (certificat SSL)
- [ ] Test du formulaire de contact
- [ ] Test de la newsletter
- [ ] Vérification des emails reçus
- [ ] .htaccess ou config Nginx en place
- [ ] Sauvegardes configurées
- [ ] Rate limiting activé (optionnel)
- [ ] reCAPTCHA activé (optionnel)
