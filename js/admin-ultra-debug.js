// Administration du blog JS Industrie - ULTRA DEBUG
let currentPassword = '';
let editingArticleId = null;

// Helper pour construire l'URL de l'API
// VERSION ULTRA DEBUG - Utilise blog-api-ultra-debug.php
function getApiUrl(action, params = {}) {
    const currentPath = window.location.pathname;
    const currentDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);

    // Pour UPDATE, utiliser la version ultra-debug
    if (action === 'update' || action === 'create') {
        const apiPath = currentDir + 'blog-api-ultra-debug.php';
        const queryParams = new URLSearchParams({ action, ...params });
        console.log('🔍 ULTRA DEBUG API URL:', `${apiPath}?${queryParams}`);
        return `${apiPath}?${queryParams}`;
    }

    // Pour les autres actions, utiliser l'API normale
    const apiPath = currentDir + 'blog-api.php';
    const queryParams = new URLSearchParams({ action, ...params });
    console.log('🔍 API URL:', `${apiPath}?${queryParams}`);
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
        console.log('📤 Envoi de la requête:', action);

        const response = await fetch(getApiUrl(action), {
            method: 'POST',
            body: formData
        });

        console.log('📥 Réponse reçue, status:', response.status);

        // Lire la réponse en texte
        const text = await response.text();
        console.log('📄 Réponse brute:', text);

        // Si c'est du HTML (version ultra-debug), l'afficher dans une popup
        if (text.includes('<pre>')) {
            const debugWindow = window.open('', 'Debug Ultra', 'width=1000,height=800,scrollbars=yes');
            debugWindow.document.write(text);
            debugWindow.document.close();

            // Vérifier si c'est un succès ou une erreur
            if (text.includes('✅ SUCCÈS') || text.includes('✅ Article mis à jour')) {
                showAlert('Article mis à jour! (fenêtre debug ouverte)', 'success');
                closeModal();
                loadArticles();
            } else {
                showAlert('Erreur - Consultez la fenêtre debug', 'error');
            }
            return;
        }

        // Sinon, parser en JSON
        const data = JSON.parse(text);
        if (data.success) {
            showAlert(id ? 'Article mis à jour avec succès !' : 'Article créé avec succès !', 'success');
            closeModal();
            loadArticles();
        } else {
            showAlert(data.message || 'Erreur lors de l\'enregistrement', 'error');
            console.error('Erreur API:', data);
        }
    } catch (error) {
        console.error('❌ Erreur:', error);
        showAlert('Erreur : ' + error.message, 'error');
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
