<?php
// Test version PHP
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTIC PHP ===\n\n";
echo "Version PHP: " . phpversion() . "\n";
echo "OS: " . PHP_OS . "\n";
echo "SAPI: " . php_sapi_name() . "\n\n";

echo "=== FONCTIONS CRITIQUES ===\n";
echo "password_hash existe: " . (function_exists('password_hash') ? 'OUI' : 'NON') . "\n";
echo "password_verify existe: " . (function_exists('password_verify') ? 'OUI' : 'NON') . "\n";
echo "getallheaders existe: " . (function_exists('getallheaders') ? 'OUI' : 'NON') . "\n";
echo "json_encode existe: " . (function_exists('json_encode') ? 'OUI' : 'NON') . "\n";
echo "json_decode existe: " . (function_exists('json_decode') ? 'OUI' : 'NON') . "\n\n";

echo "=== FICHIERS ===\n";
echo "blog-api.php existe: " . (file_exists('blog-api.php') ? 'OUI' : 'NON') . "\n";
echo "data/ existe: " . (is_dir('data') ? 'OUI' : 'NON') . "\n";
echo "data/articles.json existe: " . (file_exists('data/articles.json') ? 'OUI' : 'NON') . "\n";

if (file_exists('data/articles.json')) {
    echo "data/articles.json lisible: " . (is_readable('data/articles.json') ? 'OUI' : 'NON') . "\n";
    echo "data/articles.json accessible en écriture: " . (is_writable('data/articles.json') ? 'OUI' : 'NON') . "\n";
}

echo "\n=== TEST SIMPLE blog-api.php ===\n";
try {
    ob_start();
    $_GET['action'] = 'list';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    include('blog-api.php');
    $output = ob_get_clean();
    echo "Sortie de blog-api.php:\n";
    echo $output;
} catch (Exception $e) {
    ob_end_clean();
    echo "ERREUR: " . $e->getMessage();
}
?>
