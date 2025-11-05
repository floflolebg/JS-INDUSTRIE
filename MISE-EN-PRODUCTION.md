# 🚀 Guide de Mise en Production - JS Industrie

## ✅ Checklist de Sécurité AVANT Mise en Ligne

### 1. Configuration PHP (php.ini)

**CRITIQUE - À configurer sur le serveur :**

```ini
# Désactiver l'affichage des erreurs en production
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL

# Activer les logs d'erreurs
log_errors = On
error_log = /var/log/php/error.log

# Limiter les uploads
upload_max_filesize = 5M
post_max_size = 10M
max_execution_time = 30
memory_limit = 128M

# Sécurité
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

### 2. Permissions des Fichiers et Dossiers

```bash
# Permissions des dossiers
chmod 755 /chemin/vers/site
chmod 755 css/ js/ images/
chmod 755 blog/
chmod 755 data/
chmod 755 images/blog/

# Permissions des fichiers
chmod 644 *.html *.php *.css *.js
chmod 644 data/articles.json

# Fichiers sensibles
chmod 400 .htaccess
chmod 400 .gitignore
```

### 3. SSL/HTTPS

**OBLIGATOIRE en production :**

- ✅ Obtenir un certificat SSL (Let's Encrypt gratuit recommandé)
- ✅ Configurer le serveur pour HTTPS uniquement
- ✅ Décommenter la section HTTPS dans `.htaccess`

```bash
# Avec Let's Encrypt (certbot)
sudo certbot --apache -d votredomaine.com -d www.votredomaine.com
```

### 4. Sauvegardes Automatiques

**Mettre en place des sauvegardes régulières :**

```bash
# Exemple de script de sauvegarde (à adapter)
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf /backups/js-industrie_$DATE.tar.gz \
    /var/www/html/js-industrie \
    --exclude='*.log' \
    --exclude='temp/*'

# Garder seulement les 30 dernières sauvegardes
find /backups -name "js-industrie_*.tar.gz" -mtime +30 -delete
```

Programmer dans cron (quotidien à 2h du matin) :
```
0 2 * * * /chemin/vers/backup-script.sh
```

### 5. Surveillance et Monitoring

**Mettre en place :**

- ✅ Logs d'accès Apache/Nginx
- ✅ Logs d'erreurs PHP
- ✅ Monitoring de l'espace disque
- ✅ Alertes en cas d'erreur 500

### 6. Fichiers à SUPPRIMER en Production

**Ces fichiers NE DOIVENT PAS être sur le serveur de production :**

```
test-*.php
test-*.html
debug-*.php
generate-*.php
phpinfo.php
README.md (optionnel)
MISE-EN-PRODUCTION.md (après lecture)
```

### 7. Vérifications Finales

**Avant de mettre en ligne :**

- [ ] SSL/HTTPS actif et forcé
- [ ] Tous les fichiers de test supprimés
- [ ] Permissions correctes (755 pour dossiers, 644 pour fichiers)
- [ ] `display_errors = Off` dans php.ini
- [ ] `.htaccess` actif et testé
- [ ] Mot de passe admin changé (voir SECURITE.md)
- [ ] Adresse email dans `contact.php` correcte
- [ ] Sauvegardes automatiques configurées
- [ ] Monitoring en place

## 🔒 Sécurités en Place

### ✅ Déjà Implémentées

1. **Authentification sécurisée**
   - Mot de passe hashé avec bcrypt (12 rounds)
   - Compatible PHP 5.4+ avec polyfills
   - Fonction `password_verify()` pour la vérification

2. **Upload d'images sécurisé**
   - Limite de taille : 5MB
   - Extensions autorisées : jpg, jpeg, png, gif, webp
   - Vérification du MIME type réel
   - Vérification avec `getimagesize()`
   - Nom de fichier aléatoire (uniqid)
   - Permissions 644 après upload

3. **Headers de sécurité** (via .htaccess)
   - X-XSS-Protection
   - X-Content-Type-Options
   - X-Frame-Options
   - Content-Security-Policy
   - Referrer-Policy
   - Permissions-Policy

4. **Protection des fichiers sensibles** (via .htaccess)
   - Dossier `data/` inaccessible
   - Fichiers `.git*` protégés
   - Fichiers de test bloqués
   - Fichiers de backup bloqués

5. **Protection contre les injections**
   - Filtres de requêtes suspectes
   - Blocage de scripts malveillants dans l'URL
   - Validation stricte des entrées

6. **Formulaires sécurisés**
   - Honeypot anti-spam (contact.php)
   - Validation côté serveur
   - Échappement HTML des sorties

## 🛡️ Recommandations Supplémentaires

### Pour Plus de Sécurité

1. **WAF (Web Application Firewall)**
   - CloudFlare (gratuit) recommandé
   - Protection DDoS
   - Cache CDN
   - SSL automatique

2. **Rate Limiting**
   - Limiter les tentatives de connexion (5 max / heure)
   - Limiter les soumissions de formulaires

3. **Monitoring**
   - Uptime monitoring (UptimeRobot gratuit)
   - Alertes par email
   - Logs centralisés

4. **Mises à jour**
   - PHP à jour (minimum 7.4, idéal 8.2+)
   - Serveur web à jour
   - Vérifier régulièrement les dépendances

## 📧 Configuration Email

**Vérifier dans `contact.php` et `newsletter.php` :**

```php
define('CONTACT_EMAIL', 'votre-email@votredomaine.com');
define('FROM_EMAIL', 'noreply@votredomaine.com');
```

**Pour éviter les emails en spam :**
- Configurer SPF records DNS
- Configurer DKIM
- Utiliser un service SMTP (SendGrid, Mailgun, etc.)

## 🔐 Changer le Mot de Passe Admin

**IMPORTANT : Changez le mot de passe par défaut !**

Voir le fichier `SECURITE.md` pour les instructions détaillées.

## 📊 Tests de Sécurité

**Après mise en ligne, tester :**

1. **SSL Labs Test**
   - https://www.ssllabs.com/ssltest/
   - Viser un score A+

2. **Security Headers**
   - https://securityheaders.com/
   - Vérifier tous les headers

3. **Test de vulnérabilités**
   - https://observatory.mozilla.org/
   - Scan complet du site

4. **Tests manuels**
   - Essayer d'accéder à `/data/articles.json` → doit être bloqué
   - Essayer d'accéder à `/.git/` → doit être bloqué
   - Vérifier que HTTPS force la redirection

## 🚨 En Cas de Problème

### Site inaccessible après .htaccess

Si le site ne fonctionne plus après l'ajout du .htaccess :

1. Renommer `.htaccess` en `.htaccess.bak`
2. Recharger la page
3. Contacter l'hébergeur pour activer `mod_rewrite`

### Erreurs 500

1. Vérifier les logs PHP : `tail -f /var/log/php/error.log`
2. Vérifier les logs Apache : `tail -f /var/log/apache2/error.log`
3. Activer temporairement `display_errors` pour debug

### Upload d'images ne fonctionne pas

1. Vérifier les permissions du dossier `images/blog/` (755)
2. Vérifier `upload_max_filesize` dans php.ini
3. Vérifier que `finfo` est activé dans PHP

## 📞 Support

Pour toute question de sécurité, consulter :
- Documentation PHP : https://www.php.net/manual/fr/
- OWASP : https://owasp.org/
- Mozilla Observatory : https://observatory.mozilla.org/

---

**Dernière mise à jour :** 2025-11-05
**Site préparé pour la production ✅**
