// ===================================
// Système de notifications
// ===================================
function showNotification(message, type = 'info') {
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
            <span class="notification-message">${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

    // Ajouter les styles si ce n'est pas déjà fait
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 100px;
                right: 20px;
                min-width: 300px;
                max-width: 500px;
                padding: 1rem 1.5rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                justify-content: space-between;
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
            }
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
            .notification-success {
                border-left: 4px solid #10b981;
            }
            .notification-error {
                border-left: 4px solid #ef4444;
            }
            .notification-info {
                border-left: 4px solid #1E5BA8;
            }
            .notification-content {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            .notification-icon {
                font-size: 1.5rem;
                font-weight: bold;
            }
            .notification-success .notification-icon {
                color: #10b981;
            }
            .notification-error .notification-icon {
                color: #ef4444;
            }
            .notification-info .notification-icon {
                color: #1E5BA8;
            }
            .notification-message {
                color: #1f2937;
                line-height: 1.5;
            }
            .notification-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #6b7280;
                cursor: pointer;
                padding: 0;
                margin-left: 1rem;
                transition: color 0.3s;
            }
            .notification-close:hover {
                color: #1f2937;
            }
            @media (max-width: 768px) {
                .notification {
                    left: 20px;
                    right: 20px;
                    min-width: auto;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Ajouter au document
    document.body.appendChild(notification);

    // Fonction pour fermer la notification
    const closeNotification = () => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 300);
    };

    // Bouton de fermeture
    notification.querySelector('.notification-close').addEventListener('click', closeNotification);

    // Auto-fermeture après 5 secondes
    setTimeout(closeNotification, 5000);
}

// ===================================
// Navigation mobile
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');

            // Animation du bouton hamburger
            const spans = navToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        // Fermer le menu lors du clic sur un lien
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 968) {
                    navMenu.classList.remove('active');
                    const spans = navToggle.querySelectorAll('span');
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            });
        });
    }
});

// ===================================
// Scroll smooth pour les ancres
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        // Ne pas empêcher le comportement par défaut pour les liens qui ne pointent que vers #
        if (href === '#') {
            return;
        }

        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const headerOffset = 80;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ===================================
// Header sticky avec changement d'opacité
// ===================================
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.15)';
    } else {
        header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    }
});

// ===================================
// Filtres du blog
// ===================================
const filterButtons = document.querySelectorAll('.filter-btn');
const blogCards = document.querySelectorAll('.blog-card-full');

if (filterButtons.length > 0) {
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            const category = this.getAttribute('data-category');

            // Filtrer les articles
            blogCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'grid';
                    // Animation d'apparition
                    card.style.animation = 'fadeIn 0.5s ease-in';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// Animation fadeIn pour les filtres
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// ===================================
// Formulaire de contact
// ===================================
const contactForm = document.querySelector('.contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const button = contactForm.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        const originalBgColor = button.style.backgroundColor;

        // Désactiver le bouton et afficher le statut
        button.textContent = 'Envoi en cours...';
        button.disabled = true;

        // Récupérer les données du formulaire
        const formData = new FormData(contactForm);

        // Envoyer les données via AJAX
        fetch('contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Succès
                button.textContent = '✓ Message envoyé !';
                button.style.backgroundColor = '#10b981';

                // Réinitialiser le formulaire
                contactForm.reset();

                // Afficher un message de succès
                showNotification(data.message, 'success');
            } else {
                // Erreur
                button.textContent = '✗ Erreur';
                button.style.backgroundColor = '#ef4444';
                showNotification(data.message || 'Une erreur est survenue', 'error');
            }

            // Réinitialiser le bouton après 3 secondes
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = originalBgColor;
                button.disabled = false;
            }, 3000);
        })
        .catch(error => {
            console.error('Erreur:', error);
            button.textContent = '✗ Erreur réseau';
            button.style.backgroundColor = '#ef4444';
            showNotification('Erreur de connexion. Veuillez vérifier votre connexion internet.', 'error');

            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = originalBgColor;
                button.disabled = false;
            }, 3000);
        });
    });
}

// ===================================
// Formulaire newsletter
// ===================================
const newsletterForms = document.querySelectorAll('.newsletter-form');
newsletterForms.forEach(newsletterForm => {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const button = newsletterForm.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        const originalBgColor = button.style.backgroundColor;

        if (!emailInput.value) {
            showNotification('Veuillez entrer une adresse email', 'error');
            return;
        }

        button.textContent = 'Inscription...';
        button.disabled = true;

        // Récupérer les données du formulaire
        const formData = new FormData(newsletterForm);

        // Envoyer les données via AJAX
        fetch('newsletter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = '✓ Inscrit !';
                button.style.backgroundColor = '#10b981';
                newsletterForm.reset();
                showNotification(data.message, 'success');
            } else {
                button.textContent = '✗ Erreur';
                button.style.backgroundColor = '#ef4444';
                showNotification(data.message || 'Une erreur est survenue', 'error');
            }

            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = originalBgColor;
                button.disabled = false;
            }, 3000);
        })
        .catch(error => {
            console.error('Erreur:', error);
            button.textContent = '✗ Erreur';
            button.style.backgroundColor = '#ef4444';
            showNotification('Erreur de connexion', 'error');

            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = originalBgColor;
                button.disabled = false;
            }, 3000);
        });
    });
});

// ===================================
// Animation au scroll (Intersection Observer)
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer les cartes de services, réalisations et blog
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.service-card, .realisation-card, .blog-card, .stat-box');

    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
});

// ===================================
// Pagination du blog
// ===================================
const paginationButtons = document.querySelectorAll('.pagination-btn');
if (paginationButtons.length > 0) {
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.textContent === '→' || this.textContent === '←') {
                return;
            }

            // Retirer la classe active de tous les boutons
            paginationButtons.forEach(btn => btn.classList.remove('active'));

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            // Scroll vers le haut de la section blog
            const blogSection = document.querySelector('.blog-section');
            if (blogSection) {
                const headerOffset = 100;
                const elementPosition = blogSection.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===================================
// Effet parallaxe léger sur le hero
// ===================================
window.addEventListener('scroll', function() {
    const hero = document.querySelector('.hero');
    if (hero && window.scrollY < window.innerHeight) {
        const scrolled = window.pageYOffset;
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// ===================================
// Compteur animé pour les statistiques
// ===================================
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start) + '+';
        }
    }, 16);
}

// Observer pour les compteurs
const statsObserver = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            entry.target.classList.add('counted');
            const statBoxes = entry.target.querySelectorAll('.stat-box h4');

            statBoxes.forEach(box => {
                const text = box.textContent;
                const number = parseInt(text.replace(/\D/g, ''));
                if (number) {
                    box.textContent = '0+';
                    setTimeout(() => {
                        animateCounter(box, number);
                    }, 200);
                }
            });
        }
    });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.apropos-stats');
if (statsSection) {
    statsObserver.observe(statsSection);
}

// ===================================
// Console message
// ===================================
console.log('%c🏭 JS Industrie - Site développé avec ❤️', 'color: #2563eb; font-size: 16px; font-weight: bold;');
console.log('%cBesoin de services en chaudronnerie ? Contactez-nous !', 'color: #f59e0b; font-size: 14px;');
