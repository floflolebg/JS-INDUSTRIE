<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test du mot de passe chiffré</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔐 Test du système de mot de passe chiffré</h1>

    <div class="test-box info">
        <h2>ℹ️ Information</h2>
        <p>Le mot de passe est maintenant stocké de manière sécurisée avec bcrypt.</p>
        <p><strong>Mot de passe actuel :</strong> <code>js-industrie-admin-2024</code></p>
    </div>

    <?php
    // Inclure la configuration de blog-api.php
    define('ADMIN_PASSWORD_HASH', '$2y$12$pkPXKK1Li2F65UesuuxwoO.VL7Rqw1nGVzW9a/3yeD117rXer8vYe');

    $test_password = 'js-industrie-admin-2024';
    $wrong_password = 'mauvais-mot-de-passe';
    ?>

    <div class="test-box">
        <h2>Test 1 : Vérification du bon mot de passe</h2>
        <?php
        if (password_verify($test_password, ADMIN_PASSWORD_HASH)) {
            echo '<div class="success">✅ <strong>SUCCÈS</strong> : Le mot de passe "' . htmlspecialchars($test_password) . '" est correct !</div>';
        } else {
            echo '<div class="error">❌ <strong>ÉCHEC</strong> : Le mot de passe ne fonctionne pas.</div>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 2 : Vérification d'un mauvais mot de passe</h2>
        <?php
        if (password_verify($wrong_password, ADMIN_PASSWORD_HASH)) {
            echo '<div class="error">❌ <strong>PROBLÈME</strong> : Un mauvais mot de passe a été accepté !</div>';
        } else {
            echo '<div class="success">✅ <strong>SUCCÈS</strong> : Le mauvais mot de passe "' . htmlspecialchars($wrong_password) . '" est bien rejeté.</div>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 3 : Information sur le hash</h2>
        <?php
        $info = password_get_info(ADMIN_PASSWORD_HASH);
        ?>
        <div class="success">
            <p><strong>Algorithme :</strong> <?php echo $info['algoName']; ?></p>
            <p><strong>Hash complet :</strong></p>
            <pre><?php echo ADMIN_PASSWORD_HASH; ?></pre>
            <p><strong>Longueur :</strong> <?php echo strlen(ADMIN_PASSWORD_HASH); ?> caractères</p>
        </div>
    </div>

    <div class="test-box">
        <h2>Test 4 : Test de connexion via l'API</h2>
        <button onclick="testLogin()">Tester la connexion API</button>
        <div id="apiResult"></div>
    </div>

    <script>
        async function testLogin() {
            const result = document.getElementById('apiResult');
            result.innerHTML = '<p>Test en cours...</p>';

            try {
                const formData = new FormData();
                formData.append('password', 'js-industrie-admin-2024');

                const response = await fetch('blog-api.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    result.innerHTML = '<div class="success">✅ <strong>SUCCÈS</strong> : Connexion à l\'API réussie ! Le mot de passe chiffré fonctionne correctement.</div>';
                } else {
                    result.innerHTML = '<div class="error">❌ <strong>ÉCHEC</strong> : ' + data.message + '</div>';
                }
            } catch (error) {
                result.innerHTML = '<div class="error">❌ <strong>ERREUR</strong> : ' + error.message + '</div>';
            }
        }
    </script>

    <div class="test-box info">
        <h2>📝 Notes de sécurité</h2>
        <ul>
            <li>✅ Le hash bcrypt utilise 12 rounds (très sécurisé)</li>
            <li>✅ Le mot de passe ne peut PAS être récupéré depuis le hash</li>
            <li>✅ Chaque hash bcrypt est unique même pour le même mot de passe</li>
            <li>✅ Protection contre les attaques par force brute</li>
            <li>⚠️ Supprimez ce fichier de test après vérification pour la sécurité</li>
        </ul>
    </div>
</body>
</html>
