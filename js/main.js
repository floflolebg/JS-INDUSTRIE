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
            navToggle.classList.toggle('open');

            // Bloquer le scroll du body quand le menu est ouvert
            if (navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Fermer le menu lors du clic sur un lien
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 968) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('open');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fermer le menu lors du redimensionnement de la fenêtre
        window.addEventListener('resize', () => {
            if (window.innerWidth > 968 && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('open');
                document.body.style.overflow = '';
            }
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
// Header sticky avec changement d'opacité + Logo navigation
// ===================================
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header') || document.querySelector('.header-main');
    if (header) {
        if (window.scrollY > 50) {
            header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.15)';
        } else {
            header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        }
    }

    // Animation du logo dans la navigation
    const navLogo = document.querySelector('.nav-logo');
    const headerSection = document.querySelector('.header-logo-section') || document.querySelector('.header-title-section');

    if (navLogo && headerSection) {
        const headerRect = headerSection.getBoundingClientRect();
        // Si le header n'est plus visible (scroll de plus de 150px)
        if (window.scrollY > 150) {
            navLogo.classList.add('visible');
        } else {
            navLogo.classList.remove('visible');
        }
    } else if (navLogo) {
        // Si pas de header section, utiliser simplement le scroll
        if (window.scrollY > 150) {
            navLogo.classList.add('visible');
        } else {
            navLogo.classList.remove('visible');
        }
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
// Animation au scroll améliorée (Intersection Observer)
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const scrollObserver = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            // Ne plus observer une fois animé
            scrollObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observer les éléments avec animations
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les éléments à animer
    const fadeInElements = document.querySelectorAll('.section-title, .section-subtitle');
    const slideUpElements = document.querySelectorAll('.service-card, .realisation-card, .blog-card');
    const scaleInElements = document.querySelectorAll('.stat-box');

    // Ajouter les classes d'animation
    fadeInElements.forEach(el => {
        el.classList.add('fade-in');
        scrollObserver.observe(el);
    });

    slideUpElements.forEach((el, index) => {
        el.classList.add('slide-up');
        el.style.transitionDelay = `${index * 0.1}s`;
        scrollObserver.observe(el);
    });

    scaleInElements.forEach((el, index) => {
        el.classList.add('scale-in');
        el.style.transitionDelay = `${index * 0.15}s`;
        scrollObserver.observe(el);
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
            const statBoxes = entry.target.querySelectorAll('.stat-item h4');

            statBoxes.forEach(box => {
                const text = box.textContent;
                const number = parseInt(text.replace(/\D/g, ''));
                if (number) {
                    box.textContent = '0';
                    setTimeout(() => {
                        animateCounter(box, number);
                    }, 200);
                }
            });
        }
    });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.about-stats');
if (statsSection) {
    statsObserver.observe(statsSection);
}

// ===================================
// Particules étincelles dans le hero
// ===================================
function createSparks() {
    const hero = document.querySelector('.hero');
    if (!hero) return;

    const sparksContainer = document.createElement('div');
    sparksContainer.className = 'sparks-container';
    sparksContainer.style.cssText = `
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 2;
        overflow: hidden;
    `;
    hero.appendChild(sparksContainer);

    // Créer 30 particules
    for (let i = 0; i < 30; i++) {
        const spark = document.createElement('div');
        spark.className = 'spark';

        const size = Math.random() * 4 + 2;
        const duration = Math.random() * 3 + 2;
        const delay = Math.random() * 5;
        const startX = Math.random() * 100;
        const endX = startX + (Math.random() * 30 - 15);

        spark.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: ${Math.random() > 0.5 ? '#FFA500' : '#FFD700'};
            border-radius: 50%;
            left: ${startX}%;
            bottom: -10px;
            opacity: 0;
            box-shadow: 0 0 ${size * 2}px ${Math.random() > 0.5 ? '#FFA500' : '#FFD700'};
            animation: sparkRise ${duration}s ease-in ${delay}s infinite;
        `;

        sparksContainer.appendChild(spark);
    }

    // Ajouter l'animation CSS
    if (!document.getElementById('spark-animation')) {
        const style = document.createElement('style');
        style.id = 'spark-animation';
        style.textContent = `
            @keyframes sparkRise {
                0% {
                    transform: translateY(0) translateX(0) scale(1);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 0.8;
                }
                100% {
                    transform: translateY(-800px) translateX(${Math.random() * 100 - 50}px) scale(0);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialiser les particules au chargement
document.addEventListener('DOMContentLoaded', createSparks);

// ===================================
// Effet de brillance sur les boutons
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-primary');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.animation = 'pulse 0.6s ease-in-out';
        });
        btn.addEventListener('animationend', function() {
            this.style.animation = '';
        });
    });
});

// ===================================
// Console message
// ===================================
console.log('%c🏭 JS Industrie - Site développé avec ❤️', 'color: #2563eb; font-size: 16px; font-weight: bold;');
console.log('%cBesoin de services en chaudronnerie ? Contactez-nous !', 'color: #f59e0b; font-size: 14px;');
