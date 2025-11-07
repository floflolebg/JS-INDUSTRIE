<?php
// VERSION DEBUG de blog-api.php
// Cette version affiche TOUTES les erreurs pour diagnostiquer le problème

// Activer TOUTES les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Capturer TOUT le output
ob_start();

echo "<h1>DEBUG blog-api.php</h1>";
echo "<pre>";

echo "=== ENVIRONNEMENT ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'vide') . "\n";
echo "GET action: " . ($_GET['action'] ?? 'non défini') . "\n\n";

echo "=== FICHIERS ===\n";
echo "ARTICLES_FILE: data/articles.json\n";
echo "Existe: " . (file_exists('data/articles.json') ? 'OUI' : 'NON') . "\n";
echo "Lisible: " . (is_readable('data/articles.json') ? 'OUI' : 'NON') . "\n";
echo "Taille: " . (file_exists('data/articles.json') ? filesize('data/articles.json') : '0') . " octets\n\n";

echo "=== CONTENU articles.json ===\n";
if (file_exists('data/articles.json')) {
    $content = file_get_contents('data/articles.json');
    echo "Longueur brute: " . strlen($content) . " octets\n";
    echo "Premier caractère: " . ord($content[0]) . " (" . $content[0] . ")\n";
    echo "Dernier caractère: " . ord($content[strlen($content) - 1]) . "\n\n";

    echo "Contenu:\n";
    echo $content . "\n\n";

    echo "=== TEST JSON_DECODE ===\n";
    $decoded = json_decode($content, true);
    if ($decoded === null) {
        echo "❌ JSON_DECODE A ÉCHOUÉ!\n";
        echo "Erreur: " . json_last_error_msg() . "\n";
        echo "Code erreur: " . json_last_error() . "\n\n";
    } else {
        echo "✅ JSON décodé avec succès\n";
        echo "Nombre d'articles: " . count($decoded) . "\n\n";

        echo "=== TEST JSON_ENCODE ===\n";
        $reencoded = json_encode([
            'success' => true,
            'articles' => $decoded
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($reencoded === false) {
            echo "❌ JSON_ENCODE A ÉCHOUÉ!\n";
            echo "Erreur: " . json_last_error_msg() . "\n";
        } else {
            echo "✅ JSON réencodé avec succès\n";
            echo "Longueur: " . strlen($reencoded) . " octets\n\n";
            echo "JSON final qui DEVRAIT être retourné:\n";
            echo $reencoded . "\n\n";
        }
    }
} else {
    echo "❌ Fichier non trouvé!\n";
}

echo "=== INCLUSION DE blog-api.php ===\n";
echo "Tentative d'inclusion...\n\n";

// Nettoyer le buffer
$debug_output = ob_get_clean();

// Afficher le debug
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug blog-api.php</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        pre { background: #252526; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #4ec9b0; }
    </style>
</head>
<body>';
echo '<pre>' . htmlspecialchars($debug_output) . '</pre>';

// Maintenant tester l'API réelle
echo '<h2>TEST API RÉELLE</h2>';
echo '<pre>';

try {
    $_GET['action'] = 'list';
    ob_start();
    include('blog-api.php');
    $api_output = ob_get_clean();

    echo "Sortie de blog-api.php:\n";
    echo "Longueur: " . strlen($api_output) . " octets\n";
    echo "Premier caractère: " . (strlen($api_output) > 0 ? ord($api_output[0]) : 'vide') . "\n";
    echo "Dernier caractère: " . (strlen($api_output) > 0 ? ord($api_output[strlen($api_output) - 1]) : 'vide') . "\n\n";
    echo "Contenu:\n";
    echo htmlspecialchars($api_output);

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}

echo '</pre>';
echo '</body></html>';
?>
