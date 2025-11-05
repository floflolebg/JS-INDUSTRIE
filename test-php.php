<?php
// Test simple pour vérifier que PHP fonctionne
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'PHP fonctionne correctement !',
    'version' => phpversion(),
    'date' => date('Y-m-d H:i:s')
]);
?>
