<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Debug Connexion</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: #f9f9f9; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🔍 Debug du problème de connexion</h1>

    <div class="test-box">
        <h2>Test de connexion en direct</h2>
        <form id="testForm">
            <label>Mot de passe :
                <input type="text" id="password" value="js-industrie-admin-2024" style="width: 300px; padding: 5px;">
            </label>
            <button type="submit">Tester</button>
        </form>
        <div id="result"></div>
    </div>

    <div class="test-box">
        <h2>Détails techniques</h2>
        <pre id="details"></pre>
    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const result = document.getElementById('result');
            const details = document.getElementById('details');
            const password = document.getElementById('password').value;

            result.innerHTML = '<p>Test en cours...</p>';
            details.textContent = '';

            try {
                const formData = new FormData();
                formData.append('password', password);

                const response = await fetch('blog-api.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                details.textContent += 'Statut HTTP: ' + response.status + '\n';
                details.textContent += 'Headers: ' + JSON.stringify([...response.headers.entries()], null, 2) + '\n\n';

                const text = await response.text();
                details.textContent += 'Réponse brute:\n' + text + '\n\n';

                try {
                    const data = JSON.parse(text);
                    details.textContent += 'JSON parsé:\n' + JSON.stringify(data, null, 2);

                    if (data.success) {
                        result.innerHTML = '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;">✅ CONNEXION RÉUSSIE !</div>';
                    } else {
                        result.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;">❌ ÉCHEC: ' + data.message + '</div>';
                    }
                } catch (parseError) {
                    result.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;">❌ Erreur de parsing JSON: ' + parseError.message + '</div>';
                    details.textContent += '\nErreur de parsing: ' + parseError.message;
                }
            } catch (error) {
                result.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;">❌ Erreur réseau: ' + error.message + '</div>';
                details.textContent = 'Erreur: ' + error.message;
            }
        });

        // Auto-test au chargement
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('testForm').dispatchEvent(new Event('submit'));
            }, 500);
        });
    </script>
</body>
</html>
