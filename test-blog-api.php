<?php
// Test de l'API Blog
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Blog - JS Industrie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .test-box {
            background: #f0f0f0;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button {
            background: #1E5BA8;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #FFA500;
        }
        pre {
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 Test API Blog JS Industrie</h1>

    <div class="test-box">
        <h2>1. Test fichiers</h2>
        <?php
        $checks = [
            'blog-api.php existe' => file_exists('blog-api.php'),
            'data/ dossier existe' => is_dir('data'),
            'data/articles.json existe' => file_exists('data/articles.json'),
            'data/articles.json lisible' => is_readable('data/articles.json'),
            'data/articles.json accessible en écriture' => is_writable('data/articles.json'),
            'data/ accessible en écriture' => is_writable('data'),
        ];

        foreach ($checks as $test => $result) {
            $class = $result ? 'success' : 'error';
            $icon = $result ? '✅' : '❌';
            echo "<div class='test-box $class'>$icon $test</div>";
        }
        ?>
    </div>

    <div class="test-box">
        <h2>2. Test lecture articles</h2>
        <button onclick="testGetArticles()">Tester GET articles</button>
        <pre id="getResult"></pre>
    </div>

    <div class="test-box">
        <h2>3. Test login</h2>
        <input type="password" id="testPassword" placeholder="Mot de passe" value="js-industrie-admin-2024">
        <button onclick="testLogin()">Tester Login</button>
        <pre id="loginResult"></pre>
    </div>

    <div class="test-box">
        <h2>4. Contenu articles.json</h2>
        <?php
        if (file_exists('data/articles.json')) {
            $content = file_get_contents('data/articles.json');
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
        } else {
            echo "<p class='error'>Fichier non trouvé</p>";
        }
        ?>
    </div>

    <div class="test-box">
        <h2>5. Permissions</h2>
        <?php
        echo "<pre>";
        echo "data/ : " . substr(sprintf('%o', fileperms('data')), -4) . "\n";
        if (file_exists('data/articles.json')) {
            echo "data/articles.json : " . substr(sprintf('%o', fileperms('data/articles.json')), -4) . "\n";
        }
        echo "Propriétaire data/ : " . posix_getpwuid(fileowner('data'))['name'] . "\n";
        if (file_exists('data/articles.json')) {
            echo "Propriétaire articles.json : " . posix_getpwuid(fileowner('data/articles.json'))['name'] . "\n";
        }
        echo "User PHP : " . get_current_user() . "\n";
        echo "</pre>";
        ?>
    </div>

    <script>
        async function testGetArticles() {
            const result = document.getElementById('getResult');
            result.textContent = 'Chargement...';

            try {
                const response = await fetch('blog-api.php?action=get');
                const data = await response.json();
                result.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                result.textContent = 'ERREUR: ' + error.message;
            }
        }

        async function testLogin() {
            const result = document.getElementById('loginResult');
            const password = document.getElementById('testPassword').value;
            result.textContent = 'Test en cours...';

            const formData = new FormData();
            formData.append('password', password);

            try {
                const response = await fetch('blog-api.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                result.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                result.textContent = 'ERREUR: ' + error.message;
            }
        }
    </script>
</body>
</html>
