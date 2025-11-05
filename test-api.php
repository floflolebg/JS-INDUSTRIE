<?php
// Test de diagnostic pour l'API
echo "<h1>Test de l'API Blog</h1>";

// Test 1: PHP fonctionne
echo "<h2>✅ PHP fonctionne</h2>";
echo "<p>Version PHP: " . phpversion() . "</p>";

// Test 2: Fonction getallheaders() existe?
echo "<h2>Test getallheaders()</h2>";
if (function_exists('getallheaders')) {
    echo "<p>✅ getallheaders() existe</p>";
} else {
    echo "<p>❌ getallheaders() n'existe PAS (c'est probablement le problème !)</p>";
}

// Test 3: Dossiers et fichiers
echo "<h2>Test fichiers/dossiers</h2>";

if (is_dir('data')) {
    echo "<p>✅ Dossier data/ existe</p>";
    if (is_writable('data')) {
        echo "<p>✅ Dossier data/ est accessible en écriture</p>";
    } else {
        echo "<p>❌ Dossier data/ n'est PAS accessible en écriture</p>";
    }
} else {
    echo "<p>❌ Dossier data/ n'existe pas</p>";
}

if (file_exists('data/articles.json')) {
    echo "<p>✅ Fichier data/articles.json existe</p>";
    if (is_writable('data/articles.json')) {
        echo "<p>✅ Fichier data/articles.json est accessible en écriture</p>";
    } else {
        echo "<p>❌ Fichier data/articles.json n'est PAS accessible en écriture</p>";
    }
} else {
    echo "<p>❌ Fichier data/articles.json n'existe pas</p>";
}

if (is_dir('images/blog')) {
    echo "<p>✅ Dossier images/blog/ existe</p>";
    if (is_writable('images/blog')) {
        echo "<p>✅ Dossier images/blog/ est accessible en écriture</p>";
    } else {
        echo "<p>❌ Dossier images/blog/ n'est PAS accessible en écriture</p>";
    }
} else {
    echo "<p>❌ Dossier images/blog/ n'existe pas</p>";
}

// Test 4: Test de création d'article simple
echo "<h2>Test création d'article</h2>";
echo "<form method='POST' action='blog-api.php?action=create'>";
echo "<input type='hidden' name='password' value='js-industrie-admin-2024'>";
echo "<input type='text' name='title' value='Test Article' placeholder='Titre'><br>";
echo "<select name='category'>";
echo "<option value='actualites'>Actualités</option>";
echo "</select><br>";
echo "<textarea name='excerpt'>Ceci est un test</textarea><br>";
echo "<textarea name='content'>Contenu du test</textarea><br>";
echo "<input type='text' name='author' value='Test'><br>";
echo "<button type='submit'>Créer article de test</button>";
echo "</form>";

echo "<hr>";
echo "<h2>Test direct de l'API</h2>";
echo "<button onclick=\"testAPI()\">Tester l'API maintenant</button>";
echo "<pre id='result'></pre>";

echo "<script>
async function testAPI() {
    const result = document.getElementById('result');
    result.textContent = 'Test en cours...';

    try {
        const formData = new FormData();
        formData.append('password', 'js-industrie-admin-2024');
        formData.append('title', 'Article de test');
        formData.append('category', 'actualites');
        formData.append('excerpt', 'Ceci est un test');
        formData.append('content', 'Contenu du test');
        formData.append('author', 'Test');

        const response = await fetch('blog-api.php?action=create', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        result.textContent = 'Réponse brute du serveur:\\n' + text;

        // Essayer de parser en JSON
        try {
            const json = JSON.parse(text);
            result.textContent += '\\n\\n✅ C\\'est du JSON valide!\\n' + JSON.stringify(json, null, 2);
        } catch (e) {
            result.textContent += '\\n\\n❌ Ce n\\'est PAS du JSON valide. Erreur: ' + e.message;
        }
    } catch (error) {
        result.textContent = 'Erreur: ' + error.message;
    }
}
</script>";
?>
