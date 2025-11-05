<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test de connexion</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔐 Test de connexion au CMS</h1>

    <div class="test-box">
        <h2>Test 1: Vérification du mot de passe en PHP</h2>
        <?php
        define('ADMIN_PASSWORD', 'js-industrie-admin-2024');
        $testPassword = 'js-industrie-admin-2024';

        if ($testPassword === ADMIN_PASSWORD) {
            echo '<div class="success">✅ Le mot de passe en PHP est correct</div>';
            echo '<p>Mot de passe attendu: <code>' . ADMIN_PASSWORD . '</code></p>';
        } else {
            echo '<div class="error">❌ Problème avec le mot de passe</div>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 2: Test de l'endpoint de login</h2>
        <button onclick="testLogin()">Tester la connexion</button>
        <div id="loginResult"></div>
    </div>

    <div class="test-box">
        <h2>Test 3: Formulaire de connexion direct</h2>
        <form id="directLoginForm">
            <input type="password" id="passwordInput" placeholder="Mot de passe" value="js-industrie-admin-2024">
            <button type="submit">Se connecter</button>
        </form>
        <div id="directResult"></div>
    </div>

    <script>
        // Test 2: Appel AJAX
        async function testLogin() {
            const result = document.getElementById('loginResult');
            result.innerHTML = '<p>Test en cours...</p>';

            try {
                const formData = new FormData();
                formData.append('password', 'js-industrie-admin-2024');

                console.log('Envoi de la requête à: blog-api.php?action=login');

                const response = await fetch('blog-api.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                console.log('Statut de la réponse:', response.status);
                console.log('Headers:', [...response.headers.entries()]);

                const text = await response.text();
                console.log('Réponse brute:', text);

                result.innerHTML = `
                    <p><strong>Statut HTTP:</strong> ${response.status}</p>
                    <p><strong>Réponse brute:</strong></p>
                    <pre>${text}</pre>
                `;

                // Essayer de parser en JSON
                try {
                    const json = JSON.parse(text);
                    if (json.success) {
                        result.innerHTML += '<div class="success">✅ Connexion réussie!</div>';
                    } else {
                        result.innerHTML += '<div class="error">❌ ' + json.message + '</div>';
                    }
                } catch (e) {
                    result.innerHTML += '<div class="error">❌ La réponse n\'est pas du JSON: ' + e.message + '</div>';
                }

            } catch (error) {
                console.error('Erreur:', error);
                result.innerHTML = '<div class="error">❌ Erreur: ' + error.message + '</div>';
            }
        }

        // Test 3: Formulaire direct
        document.getElementById('directLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const result = document.getElementById('directResult');
            const password = document.getElementById('passwordInput').value;

            result.innerHTML = '<p>Connexion en cours...</p>';

            try {
                const formData = new FormData();
                formData.append('password', password);

                const response = await fetch('blog-api.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    result.innerHTML = '<div class="success">✅ Connexion réussie! Vous pouvez maintenant utiliser admin.html</div>';
                    // Sauvegarder dans sessionStorage comme admin.js
                    sessionStorage.setItem('adminPassword', password);
                } else {
                    result.innerHTML = '<div class="error">❌ ' + data.message + '</div>';
                }

            } catch (error) {
                result.innerHTML = '<div class="error">❌ Erreur: ' + error.message + '</div>';
                console.error('Erreur complète:', error);
            }
        });
    </script>
</body>
</html>
