// Chargement dynamique des articles de blog
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('articlesGrid') || document.getElementById('blog-articles-container')) {
        loadBlogArticles();
        setupFilters();
    }
});

// Charger les articles
async function loadBlogArticles(category = 'all') {
    try {
        // Construire l'URL de l'API de manière robuste
        // Obtenir le chemin du dossier actuel
        const currentPath = window.location.pathname;
        const currentDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);

        // Construire le chemin vers blog-api.php (même dossier que blog.html)
        const apiPath = currentDir + 'blog-api.php';

        const url = category === 'all'
            ? `${apiPath}?action=list`
            : `${apiPath}?action=list&category=${category}`;

        console.log('🔍 Chargement articles depuis:', url);
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            displayBlogArticles(data.articles);
        } else {
            showNoArticles();
        }
    } catch (error) {
        console.error('Erreur lors du chargement des articles:', error);
        showNoArticles();
    }
}

// Afficher les articles
function displayBlogArticles(articles) {
    const container = document.getElementById('blog-articles-container');

    if (!container) return;

    if (articles.length === 0) {
        showNoArticles();
        return;
    }

    container.innerHTML = articles.map(article => {
        const imageUrl = article.image
            ? `images/blog/${article.image}`
            : getDefaultGradient(article.category);

        const imageStyle = article.image
            ? `background-image: url('${imageUrl}'); background-size: cover; background-position: center;`
            : `background: ${imageUrl};`;

        return `
            <article class="blog-article-item" data-category="${article.category}">
                <div class="blog-article-image" style="${imageStyle}">
                    <span class="blog-article-category">${getCategoryLabel(article.category)}</span>
                </div>
                <div class="blog-article-content">
                    <div class="blog-article-meta">
                        <span>📅 ${formatDate(article.date)}</span>
                        <span>👤 Par ${article.author}</span>
                    </div>
                    <h2 class="blog-article-title">${article.title}</h2>
                    <p class="blog-article-excerpt">${article.excerpt}</p>
                    <a href="blog/articles/${article.slug}.html" class="blog-article-read-more">Lire la suite →</a>
                </div>
            </article>
        `;
    }).join('');
}

// Afficher message "pas d'articles"
function showNoArticles() {
    const container = document.getElementById('blog-articles-container');
    if (!container) return;

    container.innerHTML = `
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Aucun article disponible</h3>
            <p style="color: var(--text-light);">Les articles seront bientôt publiés !</p>
        </div>
    `;
}

// Obtenir un gradient par défaut selon la catégorie
function getDefaultGradient(category) {
    const gradients = {
        'actualites': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'projets': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'technique': 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
        'securite': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    };
    return gradients[category] || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
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

// Formater la date
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = date.getDate();
    const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
}

// Configuration des filtres
function setupFilters() {
    const filterButtons = document.querySelectorAll('.blog-filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            loadBlogArticles(category);
        });
    });
}
