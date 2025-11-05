<?php
// API pour gérer les articles de blog
// Désactiver l'affichage des erreurs pour éviter de casser le JSON
@ini_set('display_errors', 0);
@ini_set('display_startup_errors', 0);
error_reporting(0);
ini_set('log_errors', 1);

// Définir le timezone pour éviter les warnings
date_default_timezone_set('Europe/Paris');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Fichier de stockage des articles
if (!defined('ARTICLES_FILE')) {
    define('ARTICLES_FILE', 'data/articles.json');
}
if (!defined('IMAGES_DIR')) {
    define('IMAGES_DIR', 'images/blog/');
}

// Polyfill pour password_hash et password_verify (PHP 5.4 compatibility)
if (!function_exists('password_hash')) {
    function password_hash($password, $algo, $options = array()) {
        $cost = isset($options['cost']) ? $options['cost'] : 12;
        $salt = sprintf('$2y$%02d$', $cost);
        $salt .= substr(str_replace('+', '.', base64_encode(openssl_random_pseudo_bytes(16))), 0, 22);
        return crypt($password, $salt);
    }
}

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

// Hash du mot de passe admin (bcrypt)
// Mot de passe: js-industrie-admin-2024
if (!defined('ADMIN_PASSWORD_HASH')) {
    define('ADMIN_PASSWORD_HASH', '$2y$12$pkPXKK1Li2F65UesuuxwoO.VL7Rqw1nGVzW9a/3yeD117rXer8vYe');
}

// Créer les dossiers si nécessaires
if (!file_exists('data')) {
    mkdir('data', 0755);
}
if (!file_exists(IMAGES_DIR)) {
    mkdir(IMAGES_DIR, 0755, true);
}

// Fonction pour lire les articles
if (!function_exists('getArticles')) {
    function getArticles() {
        if (!file_exists(ARTICLES_FILE)) {
            return [];
        }
        $json = file_get_contents(ARTICLES_FILE);
        return json_decode($json, true) ?: [];
    }
}

// Fonction pour sauvegarder les articles
if (!function_exists('saveArticles')) {
    function saveArticles($articles) {
        return file_put_contents(ARTICLES_FILE, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// Fonction pour récupérer les headers (compatible tous serveurs)
if (!function_exists('getAllHeaders')) {
    function getAllHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Fallback pour les serveurs qui n'ont pas getallheaders()
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// Fonction pour vérifier le mot de passe
if (!function_exists('checkAuth')) {
    function checkAuth() {
        $headers = getAllHeaders();
        $password = isset($headers['X-Admin-Password']) ? $headers['X-Admin-Password'] :
                    (isset($_POST['password']) ? $_POST['password'] : '');

        if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }
    }
}

// Fonction pour générer un slug
if (!function_exists('generateSlug')) {
    function generateSlug($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}

// Fonction pour uploader une image
if (!function_exists('uploadImage')) {
    function uploadImage() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return null;
        }

        $newFilename = uniqid() . '_' . time() . '.' . $ext;
        $destination = IMAGES_DIR . $newFilename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            return $newFilename;
        }

        return null;
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// POST /blog-api.php?action=login
if ($method === 'POST' && $action === 'login') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
    }
    exit;
}

// GET /blog-api.php?action=list
if ($method === 'GET' && $action === 'list') {
    $articles = getArticles();

    // Filtrer par catégorie si demandé
    if (isset($_GET['category']) && $_GET['category'] !== 'all') {
        $category = $_GET['category'];
        $articles = array_filter($articles, function($article) use ($category) {
            return $article['category'] === $category;
        });
    }

    // Trier par date (plus récent en premier)
    usort($articles, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    echo json_encode(['success' => true, 'articles' => array_values($articles)]);
    exit;
}

// GET /blog-api.php?action=get&id=xxx
if ($method === 'GET' && $action === 'get') {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $articles = getArticles();

    foreach ($articles as $article) {
        if ($article['id'] === $id) {
            echo json_encode(['success' => true, 'article' => $article]);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
    exit;
}

// POST /blog-api.php?action=create
if ($method === 'POST' && $action === 'create') {
    checkAuth();

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : 'JS Industrie';

    if (empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants', 'debug' => [
            'title' => !empty($title),
            'category' => !empty($category),
            'excerpt' => !empty($excerpt),
            'content' => !empty($content)
        ]]);
        exit;
    }

    // Upload de l'image si présente
    $image = uploadImage();

    $articles = getArticles();
    $newArticle = [
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

    $articles[] = $newArticle;

    // Tenter de sauvegarder
    $saved = saveArticles($articles);

    if ($saved === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible d\'écrire dans data/articles.json. Vérifiez les permissions.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Article créé avec succès', 'article' => $newArticle]);
    exit;
}

// POST /blog-api.php?action=update
if ($method === 'POST' && $action === 'update') {
    checkAuth();

    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';

    if (empty($id) || empty($title) || empty($category) || empty($excerpt) || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
        exit;
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

    if ($found) {
        saveArticles($articles);
        echo json_encode(['success' => true, 'message' => 'Article mis à jour']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
    }
    exit;
}

// POST /blog-api.php?action=delete
if ($method === 'POST' && $action === 'delete') {
    checkAuth();

    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
        exit;
    }

    $articles = getArticles();
    $newArticles = array_filter($articles, function($article) use ($id) {
        return $article['id'] !== $id;
    });

    if (count($newArticles) < count($articles)) {
        saveArticles(array_values($newArticles));
        echo json_encode(['success' => true, 'message' => 'Article supprimé']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
    }
    exit;
}

// Action non reconnue
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
?>
