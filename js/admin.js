/**
 * Administration Blog - JS Industrie
 * Gestion des articles de blog
 */

let currentPassword = '';
let editingArticleId = null;

/**
 * Construire l'URL de l'API
 */
function getApiUrl(action, params = {}) {
    const path = window.location.pathname;
    const dir = path.substring(0, path.lastIndexOf('/') + 1);
    const api = dir + 'blog-api.php';
    const query = new URLSearchParams({ action, ...params });
    return `${api}?${query}`;
}

/**
 * Connexion
 */
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('loginError');
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
        errorDiv.textContent = 'Erreur de connexion';
        errorDiv.style.display = 'block';
    }
});

/**
 * Déconnexion
 */
function logout() {
    currentPassword = '';
    document.getElementById('loginScreen').style.display = 'flex';
    document.getElementById('adminContainer').style.display = 'none';
    document.getElementById('password').value = '';
}

/**
 * Charger les articles
 */
async function loadArticles() {
    try {
        const response = await fetch(getApiUrl('list'));
        const data = await response.json();

        if (data.success) {
            displayArticles(data.articles);
        }
    } catch (error) {
        showAlert('Erreur lors du chargement', 'error');
    }
}

/**
 * Afficher les articles
 */
function displayArticles(articles) {
    const grid = document.getElementById('articlesGrid');

    if (articles.length === 0) {
        grid.innerHTML = `
            <div class="no-articles">
                <h3>Aucun article</h3>
                <p>Créez votre premier article !</p>
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
                <h3>${escapeHtml(article.title)}</h3>
                <div class="article-meta">
                    <span>📅 ${formatDate(article.date)}</span>
                    <span>👤 ${escapeHtml(article.author)}</span>
                </div>
                <p>${escapeHtml(article.excerpt.substring(0, 100))}${article.excerpt.length > 100 ? '...' : ''}</p>
                <div class="article-actions">
                    <button class="btn-small btn-edit" onclick="editArticle('${article.id}')">✏️ Modifier</button>
                    <button class="btn-small btn-delete" onclick="deleteArticle('${article.id}', '${escapeHtml(article.title).replace(/'/g, "\\'")}')">🗑️ Supprimer</button>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Ouvrir le modal
 */
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

/**
 * Fermer le modal
 */
function closeModal() {
    document.getElementById('articleModal').classList.remove('active');
    editingArticleId = null;
}

/**
 * Charger un article pour édition
 */
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
        showAlert('Erreur lors du chargement', 'error');
    }
}

/**
 * Soumettre le formulaire
 */
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

        const data = await response.json();

        if (data.success) {
            showAlert(id ? 'Article mis à jour !' : 'Article créé !', 'success');
            closeModal();
            loadArticles();
        } else {
            showAlert(data.message || 'Erreur', 'error');
        }
    } catch (error) {
        showAlert('Erreur réseau', 'error');
    }
});

/**
 * Modifier un article
 */
function editArticle(id) {
    openModal(id);
}

/**
 * Supprimer un article
 */
async function deleteArticle(id, title) {
    if (!confirm(`Supprimer "${title}" ?`)) {
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
            showAlert('Article supprimé', 'success');
            loadArticles();
        } else {
            showAlert(data.message || 'Erreur', 'error');
        }
    } catch (error) {
        showAlert('Erreur réseau', 'error');
    }
}

/**
 * Afficher une alerte
 */
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

/**
 * Formater une date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Obtenir le label de catégorie
 */
function getCategoryLabel(category) {
    const labels = {
        'actualites': 'Actualités',
        'projets': 'Projets',
        'technique': 'Technique',
        'securite': 'Sécurité'
    };
    return labels[category] || category;
}

/**
 * Échapper le HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Fermer le modal en cliquant sur le fond
 */
document.getElementById('articleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
