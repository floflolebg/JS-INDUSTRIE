<?php
// VERSION ULTRA DEBUG - AFFICHE CHAQUE ÉTAPE
ini_set('display_errors', 1);
error_reporting(E_ALL);

$debug = [];
$debug[] = "=== DÉBUT DEBUG ===";
$debug[] = "Timestamp: " . date('Y-m-d H:i:s');
$debug[] = "Method: " . $_SERVER['REQUEST_METHOD'];
$debug[] = "Action: " . (isset($_GET['action']) ? $_GET['action'] : 'NONE');

// Headers (non JSON pour voir les erreurs)
header('Content-Type: text/html; charset=utf-8');

// Timezone
date_default_timezone_set('Europe/Paris');
$debug[] = "✅ Timezone défini";

// Constantes
define('ARTICLES_FILE', 'data/articles.json');
define('IMAGES_DIR', 'images/blog/');
define('ADMIN_PASSWORD_HASH', '$2y$12$pkPXKK1Li2F65UesuuxwoO.VL7Rqw1nGVzW9a/3yeD117rXer8vYe');
$debug[] = "✅ Constantes définies";

// Router
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// POST /blog-api.php?action=update
if ($method === 'POST' && $action === 'update') {
    $debug[] = "=== ACTION UPDATE ===";

    // Vérifier le mot de passe
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $debug[] = "Password reçu: " . (!empty($password) ? 'OUI (****)' : 'NON');

    if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
        $debug[] = "❌ Mot de passe incorrect";
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Authentification OK";

    // Récupérer les données
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';

    $debug[] = "Données POST:";
    $debug[] = "  - id: " . ($id ?: 'VIDE');
    $debug[] = "  - title: " . ($title ?: 'VIDE');
    $debug[] = "  - category: " . ($category ?: 'VIDE');
    $debug[] = "  - excerpt: " . (strlen($excerpt) . ' caractères');
    $debug[] = "  - content: " . (strlen($content) . ' caractères');
    $debug[] = "  - author: " . ($author ?: 'VIDE');

    // Validation
    if (empty($id) || empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        $debug[] = "❌ Champs requis manquants";
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Validation OK";

    // Lire les articles
    $debug[] = "Lecture " . ARTICLES_FILE;
    if (!file_exists(ARTICLES_FILE)) {
        $debug[] = "❌ Fichier n'existe pas";
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }

    $json = @file_get_contents(ARTICLES_FILE);
    if ($json === false) {
        $debug[] = "❌ Impossible de lire le fichier";
        $debug[] = "Erreur: " . print_r(error_get_last(), true);
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Fichier lu (" . strlen($json) . " octets)";

    $articles = json_decode($json, true);
    if ($articles === null) {
        $debug[] = "❌ JSON invalide: " . json_last_error_msg();
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ JSON décodé (" . count($articles) . " articles)";

    // Trouver l'article
    $found = false;
    $articleIndex = -1;
    foreach ($articles as $index => $article) {
        if ($article['id'] === $id) {
            $found = true;
            $articleIndex = $index;
            break;
        }
    }

    if (!$found) {
        $debug[] = "❌ Article ID=$id non trouvé";
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Article trouvé (index $articleIndex)";

    // Upload image si présente
    $newImage = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $debug[] = "Image uploadée:";
        $debug[] = "  - name: " . $_FILES['image']['name'];
        $debug[] = "  - size: " . $_FILES['image']['size'] . " octets";
        $debug[] = "  - tmp_name: " . $_FILES['image']['tmp_name'];

        // Vérifier taille
        $maxSize = 5 * 1024 * 1024;
        if ($_FILES['image']['size'] > $maxSize) {
            $debug[] = "❌ Fichier trop gros";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Taille OK";

        // Extension
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $debug[] = "❌ Extension non autorisée: $ext";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Extension OK: $ext";

        // Vérifier que c'est une image
        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
        if ($imageInfo === false) {
            $debug[] = "❌ Pas une image valide";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Image valide: " . $imageInfo[0] . "x" . $imageInfo[1];

        // Créer dossier si nécessaire
        if (!file_exists(IMAGES_DIR)) {
            $created = @mkdir(IMAGES_DIR, 0755, true);
            $debug[] = "Création " . IMAGES_DIR . ": " . ($created ? '✅' : '❌');
            if (!$created) {
                $debug[] = "Erreur: " . print_r(error_get_last(), true);
                echo "<pre>" . implode("\n", $debug) . "</pre>";
                exit;
            }
        }

        // Move uploaded file
        $newFilename = uniqid() . '_' . time() . '.' . $ext;
        $destination = IMAGES_DIR . $newFilename;
        $debug[] = "Destination: $destination";

        $moved = @move_uploaded_file($_FILES['image']['tmp_name'], $destination);
        if (!$moved) {
            $debug[] = "❌ move_uploaded_file ÉCHEC";
            $debug[] = "Erreur: " . print_r(error_get_last(), true);
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Fichier déplacé";

        @chmod($destination, 0644);
        $newImage = $newFilename;
        $debug[] = "✅ Image uploadée: $newImage";
    } else {
        $debug[] = "Pas d'image à uploader";
        if (isset($_FILES['image'])) {
            $debug[] = "  - error code: " . $_FILES['image']['error'];
        }
    }

    // Modifier l'article
    $articles[$articleIndex]['title'] = $title;
    $articles[$articleIndex]['slug'] = preg_replace('/[^a-z0-9\s-]/', '', strtolower($title));
    $articles[$articleIndex]['slug'] = preg_replace('/[\s]+/', '-', $articles[$articleIndex]['slug']);
    $articles[$articleIndex]['category'] = $category;
    $articles[$articleIndex]['excerpt'] = $excerpt;
    $articles[$articleIndex]['content'] = $content;
    $articles[$articleIndex]['author'] = $author;
    if ($newImage) {
        $articles[$articleIndex]['image'] = $newImage;
    }
    $articles[$articleIndex]['updated_at'] = date('Y-m-d H:i:s');
    $debug[] = "✅ Article modifié en mémoire";

    // Encoder JSON
    $encoded = json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        $debug[] = "❌ JSON_ENCODE ÉCHEC: " . json_last_error_msg();
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ JSON encodé (" . strlen($encoded) . " octets)";

    // Écrire le fichier
    $debug[] = "Écriture dans " . ARTICLES_FILE;
    $written = @file_put_contents(ARTICLES_FILE, $encoded);
    if ($written === false) {
        $debug[] = "❌ FILE_PUT_CONTENTS ÉCHEC";
        $debug[] = "Erreur: " . print_r(error_get_last(), true);
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Fichier écrit ($written octets)";

    // Vérifier
    $verify = @file_get_contents(ARTICLES_FILE);
    if ($verify === $encoded) {
        $debug[] = "✅ Vérification: OK";
    } else {
        $debug[] = "⚠️ Vérification: Différence";
        $debug[] = "  Attendu: " . strlen($encoded) . " octets";
        $debug[] = "  Lu: " . strlen($verify) . " octets";
    }

    $debug[] = "=== SUCCÈS ===";
    echo "<pre style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50;'>";
    echo implode("\n", $debug);
    echo "\n\n<strong>✅ Article mis à jour avec succès!</strong>";
    echo "</pre>";
    exit;
}

// CREATE
if ($method === 'POST' && $action === 'create') {
    $debug[] = "=== ACTION CREATE ===";

    // Vérifier le mot de passe
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $debug[] = "Password reçu: " . (!empty($password) ? 'OUI (****)' : 'NON');

    if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
        $debug[] = "❌ Mot de passe incorrect";
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Authentification OK";

    // Récupérer les données
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : 'JS Industrie';

    $debug[] = "Données POST:";
    $debug[] = "  - title: " . ($title ?: 'VIDE');
    $debug[] = "  - category: " . ($category ?: 'VIDE');
    $debug[] = "  - excerpt: " . (strlen($excerpt) . ' caractères');
    $debug[] = "  - content: " . (strlen($content) . ' caractères');
    $debug[] = "  - author: " . ($author ?: 'VIDE');

    // Validation
    if (empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        $debug[] = "❌ Champs requis manquants";
        $debug[] = "  title vide: " . (empty($title) ? 'OUI' : 'NON');
        $debug[] = "  category vide: " . (empty($category) ? 'OUI' : 'NON');
        $debug[] = "  excerpt vide: " . (empty($excerpt) ? 'OUI' : 'NON');
        $debug[] = "  content vide: " . (empty($content) ? 'OUI' : 'NON');
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ Validation OK";

    // Upload image si présente
    $uploadedImage = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $debug[] = "Image uploadée:";
        $debug[] = "  - name: " . $_FILES['image']['name'];
        $debug[] = "  - size: " . $_FILES['image']['size'] . " octets";
        $debug[] = "  - tmp_name: " . $_FILES['image']['tmp_name'];

        // Vérifier taille
        $maxSize = 5 * 1024 * 1024;
        if ($_FILES['image']['size'] > $maxSize) {
            $debug[] = "❌ Fichier trop gros";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Taille OK";

        // Extension
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $debug[] = "❌ Extension non autorisée: $ext";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Extension OK: $ext";

        // Vérifier que c'est une image
        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
        if ($imageInfo === false) {
            $debug[] = "❌ Pas une image valide";
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Image valide: " . $imageInfo[0] . "x" . $imageInfo[1];

        // Créer dossier si nécessaire
        if (!file_exists(IMAGES_DIR)) {
            $created = @mkdir(IMAGES_DIR, 0755, true);
            $debug[] = "Création " . IMAGES_DIR . ": " . ($created ? '✅' : '❌');
            if (!$created) {
                $debug[] = "Erreur: " . print_r(error_get_last(), true);
                echo "<pre>" . implode("\n", $debug) . "</pre>";
                exit;
            }
        }

        // Move uploaded file
        $newFilename = uniqid() . '_' . time() . '.' . $ext;
        $destination = IMAGES_DIR . $newFilename;
        $debug[] = "Destination: $destination";

        $moved = @move_uploaded_file($_FILES['image']['tmp_name'], $destination);
        if (!$moved) {
            $debug[] = "❌ move_uploaded_file ÉCHEC";
            $debug[] = "Erreur: " . print_r(error_get_last(), true);
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Fichier déplacé";

        @chmod($destination, 0644);
        $uploadedImage = $newFilename;
        $debug[] = "✅ Image uploadée: $uploadedImage";
    } else {
        $debug[] = "Pas d'image à uploader";
        if (isset($_FILES['image'])) {
            $debug[] = "  - error code: " . $_FILES['image']['error'];
        }
    }

    // Lire les articles existants
    $debug[] = "Lecture " . ARTICLES_FILE;

    // Créer dossier data si nécessaire
    if (!file_exists('data')) {
        $created = @mkdir('data', 0755);
        $debug[] = "Création dossier data/: " . ($created ? '✅' : '❌');
        if (!$created) {
            $debug[] = "Erreur: " . print_r(error_get_last(), true);
        }
    }

    $articles = [];
    if (file_exists(ARTICLES_FILE)) {
        $json = @file_get_contents(ARTICLES_FILE);
        if ($json === false) {
            $debug[] = "❌ Impossible de lire le fichier";
            $debug[] = "Erreur: " . print_r(error_get_last(), true);
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ Fichier lu (" . strlen($json) . " octets)";

        $articles = json_decode($json, true);
        if ($articles === null) {
            $debug[] = "❌ JSON invalide: " . json_last_error_msg();
            echo "<pre>" . implode("\n", $debug) . "</pre>";
            exit;
        }
        $debug[] = "✅ JSON décodé (" . count($articles) . " articles existants)";
    } else {
        $debug[] = "⚠️ Fichier n'existe pas, création d'un nouveau";
    }

    // Créer le nouvel article
    $newArticle = [
        'id' => uniqid(),
        'slug' => preg_replace('/[^a-z0-9\s-]/', '', strtolower($title)),
        'title' => $title,
        'category' => $category,
        'excerpt' => $excerpt,
        'content' => $content,
        'author' => $author,
        'image' => $uploadedImage,
        'date' => date('Y-m-d H:i:s'),
        'views' => 0
    ];
    $newArticle['slug'] = preg_replace('/[\s]+/', '-', $newArticle['slug']);
    $newArticle['slug'] = trim($newArticle['slug'], '-');

    $debug[] = "✅ Nouvel article créé:";
    $debug[] = "  - id: " . $newArticle['id'];
    $debug[] = "  - slug: " . $newArticle['slug'];
    $debug[] = "  - image: " . ($newArticle['image'] ?: 'aucune');

    // Ajouter l'article
    $articles[] = $newArticle;
    $debug[] = "✅ Article ajouté au tableau (total: " . count($articles) . " articles)";

    // Encoder JSON
    $encoded = json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        $debug[] = "❌ JSON_ENCODE ÉCHEC: " . json_last_error_msg();
        echo "<pre>" . implode("\n", $debug) . "</pre>";
        exit;
    }
    $debug[] = "✅ JSON encodé (" . strlen($encoded) . " octets)";

    // Écrire le fichier
    $debug[] = "Écriture dans " . ARTICLES_FILE;
    $debug[] = "Chemin absolu: " . realpath(dirname(ARTICLES_FILE)) . '/' . basename(ARTICLES_FILE);

    $written = @file_put_contents(ARTICLES_FILE, $encoded);
    if ($written === false) {
        $debug[] = "❌ FILE_PUT_CONTENTS ÉCHEC";
        $error = error_get_last();
        $debug[] = "Erreur: " . print_r($error, true);

        // Diagnostic supplémentaire
        $debug[] = "Diagnostic:";
        $debug[] = "  - Dossier data/ existe: " . (file_exists('data') ? 'OUI' : 'NON');
        $debug[] = "  - data/ écriture: " . (is_writable('data') ? 'OUI' : 'NON');
        if (file_exists(ARTICLES_FILE)) {
            $debug[] = "  - articles.json écriture: " . (is_writable(ARTICLES_FILE) ? 'OUI' : 'NON');
            $debug[] = "  - articles.json permissions: " . substr(sprintf('%o', fileperms(ARTICLES_FILE)), -4);
        }

        echo "<pre style='background: #ffebee; padding: 20px; border-left: 4px solid #f44336;'>";
        echo implode("\n", $debug);
        echo "</pre>";
        exit;
    }
    $debug[] = "✅ Fichier écrit ($written octets)";

    // Vérifier
    $verify = @file_get_contents(ARTICLES_FILE);
    if ($verify === $encoded) {
        $debug[] = "✅ Vérification: OK";
    } else {
        $debug[] = "⚠️ Vérification: Différence";
        $debug[] = "  Attendu: " . strlen($encoded) . " octets";
        $debug[] = "  Lu: " . strlen($verify) . " octets";
    }

    $debug[] = "=== SUCCÈS ===";
    echo "<pre style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50;'>";
    echo implode("\n", $debug);
    echo "\n\n<strong>✅ Article créé avec succès!</strong>";
    echo "\n<strong>ID:</strong> " . $newArticle['id'];
    echo "\n<strong>Slug:</strong> " . $newArticle['slug'];
    echo "</pre>";
    exit;
}

// Autres actions
$debug[] = "❌ Action non gérée: $action";
echo "<pre>" . implode("\n", $debug) . "</pre>";
?>
