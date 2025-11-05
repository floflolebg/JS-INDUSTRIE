# 🔐 Sécurité - Mot de passe administrateur

## Mot de passe actuel

Le mot de passe pour accéder à l'administration du blog est :

```
js-industrie-admin-2024
```

**⚠️ IMPORTANT** : Le mot de passe est stocké de manière sécurisée avec **bcrypt** (algorithme de hachage).

## Comment changer le mot de passe

Si vous souhaitez changer le mot de passe administrateur :

### Méthode 1 : Avec PHP en ligne de commande

1. Créez un fichier `nouveau-hash.php` :
```php
<?php
$nouveau_password = 'VOTRE-NOUVEAU-MOT-DE-PASSE';
$hash = password_hash($nouveau_password, PASSWORD_BCRYPT);
echo "Nouveau hash : $hash\n";
?>
```

2. Exécutez le script :
```bash
php nouveau-hash.php
```

3. Copiez le hash généré

4. Remplacez le hash dans `blog-api.php` ligne 28 :
```php
define('ADMIN_PASSWORD_HASH', 'COLLEZ_LE_NOUVEAU_HASH_ICI');
```

5. Supprimez le fichier `nouveau-hash.php` pour la sécurité

### Méthode 2 : Avec un outil en ligne

1. Allez sur https://bcrypt-generator.com/
2. Entrez votre nouveau mot de passe
3. Sélectionnez "12" comme nombre de rounds
4. Copiez le hash généré (commence par `$2y$12$`)
5. Remplacez-le dans `blog-api.php` ligne 28

## Sécurité

✅ Le mot de passe est hashé avec **bcrypt** (algorithme à sens unique)
✅ Impossible de retrouver le mot de passe à partir du hash
✅ Protection contre les attaques par force brute (12 rounds bcrypt)
✅ Le mot de passe n'est jamais stocké en clair dans la base de données

## Recommandations

- ✅ Utilisez un mot de passe fort (12+ caractères, majuscules, minuscules, chiffres, symboles)
- ✅ Changez le mot de passe régulièrement
- ❌ Ne partagez jamais votre mot de passe
- ❌ Ne commitez jamais le mot de passe en clair dans Git
