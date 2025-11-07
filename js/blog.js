// Chargement dynamique des articles de blog
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('articlesGrid') || document.querySelector('.blog-grid-full')) {
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
    const gridFull = document.querySelector('.blog-grid-full');

    if (!gridFull) return;

    if (articles.length === 0) {
        showNoArticles();
        return;
    }

    gridFull.innerHTML = articles.map(article => {
        const imageUrl = article.image
            ? `images/blog/${article.image}`
            : getDefaultGradient(article.category);

        const imageStyle = article.image
            ? `background-image: url('${imageUrl}'); background-size: cover; background-position: center;`
            : `background: ${imageUrl};`;

        return `
            <article class="blog-card-full" data-category="${article.category}">
                <div class="blog-image-full" style="${imageStyle}">
                    <span class="blog-category">${getCategoryLabel(article.category)}</span>
                </div>
                <div class="blog-content-full">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            ${formatDate(article.date)}
                        </span>
                        <span class="blog-author">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Par ${article.author}
                        </span>
                    </div>
                    <h2>${article.title}</h2>
                    <p>${article.excerpt}</p>
                    <a href="blog/articles/${article.slug}.html" class="btn btn-secondary">Lire l'article complet →</a>
                </div>
            </article>
        `;
    }).join('');
}

// Afficher message "pas d'articles"
function showNoArticles() {
    const gridFull = document.querySelector('.blog-grid-full');
    if (!gridFull) return;

    gridFull.innerHTML = `
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; grid-column: 1 / -1;">
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
    const filterButtons = document.querySelectorAll('.filter-btn');

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
