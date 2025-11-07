# 🔧 Diagnostic - Administration Blog ne fonctionne plus

## 🔐 Mot de passe admin

```
js-industrie-admin-2024
```

## 🧪 Comment diagnostiquer le problème

### Étape 1 : Tester le fichier de diagnostic

1. Uploadez le fichier `test-blog-api.php` sur votre hébergement
2. Allez sur : `https://votre-site.fr/test-blog-api.php`
3. Vérifiez tous les tests :
   - ✅ Tous les fichiers existent ?
   - ✅ Permissions d'écriture sur data/articles.json ?
   - ✅ L'API répond correctement ?
   - ✅ Le login fonctionne ?

### Étape 2 : Vérifier la console du navigateur

1. Allez sur : `https://votre-site.fr/admin.html`
2. Appuyez sur **F12** pour ouvrir les outils de développement
3. Allez dans l'onglet **Console**
4. Connectez-vous avec le mot de passe
5. Notez toutes les erreurs en rouge

### Étape 3 : Vérifier l'onglet Network

1. Toujours dans F12, allez dans l'onglet **Network** / **Réseau**
2. Rechargez la page
3. Connectez-vous
4. Cherchez les requêtes vers `blog-api.php`
5. Cliquez dessus et vérifiez :
   - **Status** : doit être 200 (pas 403, 404 ou 500)
   - **Response** : doit être du JSON valide
   - **Headers** : Content-Type doit être `application/json`

## ⚠️ Problèmes courants

### Problème 1 : Erreur 403 Forbidden

**Cause** : Le fichier .htaccess bloque l'accès

**Solution** :
```apache
# Dans .htaccess, vérifiez cette section :
<DirectoryMatch "^.*/data/.*">
    Require all denied
</DirectoryMatch>
```

Cette règle est normale, elle protège le dossier data/ d'un accès direct. L'API y accède via PHP.

### Problème 2 : Erreur 500 ou JSON invalide

**Cause** : Erreur PHP dans blog-api.php

**Solution** :
1. Activez temporairement les erreurs PHP en haut de `blog-api.php` :
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

2. Rechargez admin.html et regardez les erreurs
3. Une fois corrigé, remettez :
```php
ini_set('display_errors', 0);
error_reporting(0);
```

### Problème 3 : "Cannot read properties of undefined"

**Cause** : Erreur JavaScript dans admin.js

**Solution** : Vérifiez que tous les fichiers sont à jour :
- admin.html
- js/admin.js
- blog-api.php

### Problème 4 : Permissions fichiers

**Cause** : data/articles.json n'est pas accessible en écriture

**Solution** via FTP/SSH :
```bash
chmod 755 data
chmod 644 data/articles.json
```

Ou via cPanel File Manager :
1. Clic droit sur le dossier `data/`
2. Permissions → 755
3. Clic droit sur `data/articles.json`
4. Permissions → 644

### Problème 5 : CORS / Headers

**Cause** : Le serveur bloque les requêtes AJAX

**Solution** : Vérifiez que `blog-api.php` contient bien :
```php
header('Content-Type: application/json; charset=utf-8');
```

## 📋 Checklist de vérification

- [ ] Le fichier blog-api.php est uploadé
- [ ] Le dossier data/ existe avec permissions 755
- [ ] Le fichier data/articles.json existe avec permissions 644
- [ ] Le fichier js/admin.js est uploadé et accessible
- [ ] Le mot de passe est correct : `js-industrie-admin-2024`
- [ ] Aucune erreur dans la console (F12)
- [ ] Le fichier .htaccess n'est pas trop restrictif
- [ ] PHP fonctionne sur l'hébergement (version 5.4+)

## 🔍 Commandes de diagnostic

Si vous avez accès SSH :

```bash
# Vérifier permissions
ls -la data/
ls -la data/articles.json

# Tester l'API
curl https://votre-site.fr/blog-api.php?action=get

# Vérifier PHP
php -v
php -r "echo json_encode(['test' => 'ok']);"
```

## 📞 Informations à fournir si problème persiste

Si le problème persiste, envoyez-moi :

1. **Capture d'écran de la console (F12)**
2. **Capture de l'onglet Network montrant la requête à blog-api.php**
3. **Résultat du test-blog-api.php**
4. **Version de PHP de votre hébergeur** (visible dans cPanel ou phpinfo)
5. **Le contenu exact des erreurs**

## ✅ Solution rapide

Si tout semble correct mais ça ne marche toujours pas :

1. **Supprimez le cache du navigateur** : Ctrl + Shift + Suppr
2. **Rechargez en mode privé** : Ctrl + Shift + N
3. **Testez avec un autre navigateur**
4. **Vérifiez que vous êtes sur la bonne URL** (pas en local)
5. **Re-uploadez tous les fichiers** depuis le dernier commit GitHub
