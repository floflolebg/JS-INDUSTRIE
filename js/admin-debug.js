// Administration du blog JS Industrie
let currentPassword = '';
let editingArticleId = null;

// Helper pour construire l'URL de l'API
// VERSION DEBUG - Utilise blog-api-errors.php pour voir les erreurs PHP
function getApiUrl(action, params = {}) {
    const currentPath = window.location.pathname;
    const currentDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
    const apiPath = currentDir + 'blog-api-errors.php'; // VERSION DEBUG

    const queryParams = new URLSearchParams({ action, ...params });
    console.log('🔍 DEBUG API URL:', `${apiPath}?${queryParams}`);
    return `${apiPath}?${queryParams}`;
}

// Connexion
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('loginError');

    // Vérification du mot de passe
    const formData = new FormData();
    formData.append('password', password);

    try {
        const response = await fetch(getApiUrl('login'), {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            currentPassword = password;
            document.getElementById('loginScreen').style.display = 'none';
            document.getElementById('adminContainer').style.display = 'block';
            loadArticles();
        } else {
            errorDiv.textContent = data.message || 'Mot de passe incorrect';
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.textContent = 'Erreur de connexion au serveur';
        errorDiv.style.display = 'block';
    }
});

// Déconnexion
function logout() {
    currentPassword = '';
    document.getElementById('loginScreen').style.display = 'flex';
    document.getElementById('adminContainer').style.display = 'none';
    document.getElementById('password').value = '';
}

// Charger les articles
async function loadArticles() {
    try {
        const response = await fetch(getApiUrl('list'));
        const data = await response.json();

        if (data.success) {
            displayArticles(data.articles);
        }
    } catch (error) {
        showAlert('Erreur lors du chargement des articles', 'error');
    }
}

// Afficher les articles
function displayArticles(articles) {
    const grid = document.getElementById('articlesGrid');

    if (articles.length === 0) {
        grid.innerHTML = `
            <div class="no-articles">
                <h3>Aucun article</h3>
                <p>Commencez par créer votre premier article !</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = articles.map(article => `
        <div class="article-card">
            <div class="article-image" style="background-image: url('${article.image ? 'images/blog/' + article.image : 'images/logo1.png'}'); ${!article.image ? 'background-size: contain; background-repeat: no-repeat;' : ''}">
                <span class="article-category">${getCategoryLabel(article.category)}</span>
            </div>
            <div class="article-body">
                <h3>${article.title}</h3>
                <div class="article-meta">
                    <span>📅 ${formatDate(article.date)}</span>
                    <span>👤 ${article.author}</span>
                </div>
                <p>${article.excerpt.substring(0, 100)}${article.excerpt.length > 100 ? '...' : ''}</p>
                <div class="article-actions">
                    <button class="btn-small btn-edit" onclick="editArticle('${article.id}')">✏️ Modifier</button>
                    <button class="btn-small btn-delete" onclick="deleteArticle('${article.id}', '${article.title.replace(/'/g, "\\'")}')">🗑️ Supprimer</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Ouvrir le modal
function openModal(articleId = null) {
    editingArticleId = articleId;
    const modal = document.getElementById('articleModal');
    const form = document.getElementById('articleForm');

    if (articleId) {
        document.getElementById('modalTitle').textContent = 'Modifier l\'article';
        loadArticleForEdit(articleId);
    } else {
        document.getElementById('modalTitle').textContent = 'Nouvel Article';
        form.reset();
        document.getElementById('articleId').value = '';
        document.getElementById('author').value = 'JS Industrie';
    }

    modal.classList.add('active');
}

// Fermer le modal
function closeModal() {
    document.getElementById('articleModal').classList.remove('active');
    editingArticleId = null;
}

// Charger un article pour édition
async function loadArticleForEdit(id) {
    try {
        const response = await fetch(getApiUrl('get', { id }));
        const data = await response.json();

        if (data.success) {
            const article = data.article;
            document.getElementById('articleId').value = article.id;
            document.getElementById('title').value = article.title;
            document.getElementById('category').value = article.category;
            document.getElementById('author').value = article.author;
            document.getElementById('excerpt').value = article.excerpt;
            document.getElementById('content').value = article.content;
        }
    } catch (error) {
        showAlert('Erreur lors du chargement de l\'article', 'error');
    }
}

// Soumettre le formulaire
document.getElementById('articleForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData();
    const id = document.getElementById('articleId').value;

    formData.append('password', currentPassword);
    formData.append('title', document.getElementById('title').value);
    formData.append('category', document.getElementById('category').value);
    formData.append('author', document.getElementById('author').value);
    formData.append('excerpt', document.getElementById('excerpt').value);
    formData.append('content', document.getElementById('content').value);

    if (id) {
        formData.append('id', id);
    }

    const imageFile = document.getElementById('image').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    try {
        const action = id ? 'update' : 'create';
        const response = await fetch(getApiUrl(action), {
            method: 'POST',
            body: formData
        });

        // Lire la réponse en texte d'abord
        const text = await response.text();
        console.log('📥 Réponse brute du serveur:', text);

        // Vérifier si la réponse est du JSON valide
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // La réponse n'est pas du JSON, probablement une erreur PHP
            console.error('❌ Réponse non-JSON reçue:');
            console.error(text);

            // Créer une popup avec l'erreur complète
            const errorWindow = window.open('', 'Erreur PHP', 'width=800,height=600,scrollbars=yes');
            errorWindow.document.write('<html><head><title>Erreur PHP Complète</title></head><body>');
            errorWindow.document.write('<h1 style="color: red;">Erreur PHP</h1>');
            errorWindow.document.write('<pre style="background: #f5f5f5; padding: 20px; overflow-x: auto;">');
            errorWindow.document.write(text.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
            errorWindow.document.write('</pre></body></html>');
            errorWindow.document.close();

            showAlert('Erreur PHP - Une fenêtre avec les détails s\'est ouverte', 'error');
            return;
        }

        // Parser le JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('❌ JSON Parse Error:', e);
            console.error('Texte reçu:', text);

            // Créer une popup avec l'erreur
            const errorWindow = window.open('', 'Erreur JSON', 'width=800,height=600,scrollbars=yes');
            errorWindow.document.write('<html><head><title>Erreur JSON</title></head><body>');
            errorWindow.document.write('<h1 style="color: red;">Erreur de parsing JSON</h1>');
            errorWindow.document.write('<p>Le serveur a retourné une réponse invalide:</p>');
            errorWindow.document.write('<pre style="background: #f5f5f5; padding: 20px; overflow-x: auto;">');
            errorWindow.document.write(text.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
            errorWindow.document.write('</pre></body></html>');
            errorWindow.document.close();

            showAlert('Erreur JSON - Une fenêtre avec les détails s\'est ouverte', 'error');
            return;
        }

        if (data.success) {
            showAlert(id ? 'Article mis à jour avec succès !' : 'Article créé avec succès !', 'success');
            closeModal();
            loadArticles();
        } else {
            showAlert(data.message || 'Erreur lors de l\'enregistrement', 'error');
            console.error('Erreur API:', data);
        }
    } catch (error) {
        console.error('❌ Erreur complète:', error);
        showAlert('Erreur réseau : ' + error.message, 'error');
    }
});

// Modifier un article
function editArticle(id) {
    openModal(id);
}

// Supprimer un article
async function deleteArticle(id, title) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'article "${title}" ?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('password', currentPassword);
    formData.append('id', id);

    try {
        const response = await fetch(getApiUrl('delete'), {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Article supprimé avec succès', 'success');
            loadArticles();
        } else {
            showAlert(data.message || 'Erreur lors de la suppression', 'error');
        }
    } catch (error) {
        showAlert('Erreur réseau', 'error');
    }
}

// Afficher une alerte
function showAlert(message, type) {
    const alertDiv = document.getElementById('alert');
    alertDiv.innerHTML = `
        <div class="alert alert-${type === 'error' ? 'error' : 'success'}">
            ${message}
        </div>
    `;

    setTimeout(() => {
        alertDiv.innerHTML = '';
    }, 5000);
}

// Formater la date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Obtenir le label de catégorie
function getCategoryLabel(category) {
    const labels = {
        'actualites': 'Actualités',
        'projets': 'Projets',
        'technique': 'Technique',
        'securite': 'Sécurité'
    };
    return labels[category] || category;
}

// Fermer le modal en cliquant sur le fond
document.getElementById('articleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
