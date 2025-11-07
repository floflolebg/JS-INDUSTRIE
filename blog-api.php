<?php
/**
 * API REST pour gérer les articles de blog
 * JS Industrie - 2025
 */

// Configuration stricte
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Paris');

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Constantes
define('ARTICLES_FILE', 'data/articles.json');
define('IMAGES_DIR', 'images/blog/');
define('ADMIN_PASSWORD', '$2y$12$pkPXKK1Li2F65UesuuxwoO.VL7Rqw1nGVzW9a/3yeD117rXer8vYe');

// Polyfills password PHP 5.4
if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

/**
 * Envoyer une réponse JSON propre
 */
function respond($data, $code = 200) {
    http_response_code($code);
    die(json_encode($data));
}

/**
 * Lire les articles
 */
function getArticles() {
    if (!file_exists(ARTICLES_FILE)) {
        return [];
    }
    $json = @file_get_contents(ARTICLES_FILE);
    return $json ? json_decode($json, true) : [];
}

/**
 * Sauvegarder les articles
 */
function saveArticles($articles) {
    if (!file_exists('data')) {
        @mkdir('data', 0755, true);
    }
    return @file_put_contents(
        ARTICLES_FILE,
        json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

/**
 * Vérifier authentification
 */
function checkAuth() {
    $password = $_POST['password'] ?? '';
    if (!password_verify($password, ADMIN_PASSWORD)) {
        respond(['success' => false, 'message' => 'Non autorisé'], 401);
    }
}

/**
 * Générer un slug
 */
function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Uploader une image
 */
function uploadImage() {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES['image'];

    // Vérifier taille (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    // Vérifier extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return null;
    }

    // Vérifier que c'est une vraie image
    if (!@getimagesize($file['tmp_name'])) {
        return null;
    }

    // Créer dossier si nécessaire
    if (!file_exists(IMAGES_DIR)) {
        @mkdir(IMAGES_DIR, 0755, true);
    }

    // Déplacer fichier
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = IMAGES_DIR . $filename;

    if (@move_uploaded_file($file['tmp_name'], $destination)) {
        @chmod($destination, 0644);
        return $filename;
    }

    return null;
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============================================================
// LOGIN
// ============================================================
if ($method === 'POST' && $action === 'login') {
    $password = $_POST['password'] ?? '';

    if (password_verify($password, ADMIN_PASSWORD)) {
        respond(['success' => true, 'message' => 'Connexion réussie']);
    }

    respond(['success' => false, 'message' => 'Mot de passe incorrect'], 401);
}

// ============================================================
// LIST
// ============================================================
if ($method === 'GET' && $action === 'list') {
    $articles = getArticles();

    // Filtrer par catégorie
    if (isset($_GET['category']) && $_GET['category'] !== 'all') {
        $category = $_GET['category'];
        $articles = array_filter($articles, function($a) use ($category) {
            return $a['category'] === $category;
        });
    }

    // Trier par date
    usort($articles, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    respond(['success' => true, 'articles' => array_values($articles)]);
}

// ============================================================
// GET
// ============================================================
if ($method === 'GET' && $action === 'get') {
    $id = $_GET['id'] ?? '';
    $articles = getArticles();

    foreach ($articles as $article) {
        if ($article['id'] === $id) {
            respond(['success' => true, 'article' => $article]);
        }
    }

    respond(['success' => false, 'message' => 'Article non trouvé'], 404);
}

// ============================================================
// CREATE
// ============================================================
if ($method === 'POST' && $action === 'create') {
    checkAuth();

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'JS Industrie');

    // Validation
    if (empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        respond(['success' => false, 'message' => 'Champs requis manquants'], 400);
    }

    // Upload image
    $image = uploadImage();

    // Créer article
    $article = [
        'id' => uniqid(),
        'slug' => generateSlug($title),
        'title' => $title,
        'category' => $category,
        'excerpt' => $excerpt,
        'content' => $content,
        'author' => $author,
        'image' => $image,
        'date' => date('Y-m-d H:i:s'),
        'views' => 0
    ];

    // Sauvegarder
    $articles = getArticles();
    $articles[] = $article;

    if (!saveArticles($articles)) {
        respond(['success' => false, 'message' => 'Erreur d\'écriture'], 500);
    }

    respond(['success' => true, 'message' => 'Article créé', 'article' => $article]);
}

// ============================================================
// UPDATE
// ============================================================
if ($method === 'POST' && $action === 'update') {
    checkAuth();

    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');

    // Validation
    if (empty($id) || empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        respond(['success' => false, 'message' => 'Champs requis manquants'], 400);
    }

    $articles = getArticles();
    $found = false;

    foreach ($articles as &$article) {
        if ($article['id'] === $id) {
            // Upload nouvelle image si présente
            $newImage = uploadImage();

            $article['title'] = $title;
            $article['slug'] = generateSlug($title);
            $article['category'] = $category;
            $article['excerpt'] = $excerpt;
            $article['content'] = $content;
            $article['author'] = $author;

            if ($newImage) {
                $article['image'] = $newImage;
            }

            $article['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    if (!$found) {
        respond(['success' => false, 'message' => 'Article non trouvé'], 404);
    }

    if (!saveArticles($articles)) {
        respond(['success' => false, 'message' => 'Erreur d\'écriture'], 500);
    }

    respond(['success' => true, 'message' => 'Article mis à jour']);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'POST' && $action === 'delete') {
    checkAuth();

    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        respond(['success' => false, 'message' => 'ID manquant'], 400);
    }

    $articles = getArticles();
    $newArticles = array_filter($articles, function($a) use ($id) {
        return $a['id'] !== $id;
    });

    if (count($newArticles) === count($articles)) {
        respond(['success' => false, 'message' => 'Article non trouvé'], 404);
    }

    saveArticles(array_values($newArticles));
    respond(['success' => true, 'message' => 'Article supprimé']);
}

// ============================================================
// ACTION NON RECONNUE
// ============================================================
respond(['success' => false, 'message' => 'Action non reconnue'], 400);
