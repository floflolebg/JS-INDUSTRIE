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

        // Récupérer les données du formulaire
        const formData = new FormData(contactForm);

        // Afficher un message de confirmation (à remplacer par un vrai envoi AJAX)
        const button = contactForm.querySelector('button[type="submit"]');
        const originalText = button.textContent;

        button.textContent = 'Envoi en cours...';
        button.disabled = true;

        // Simuler l'envoi (à remplacer par un vrai appel API)
        setTimeout(() => {
            button.textContent = '✓ Message envoyé !';
            button.style.backgroundColor = '#10b981';

            // Réinitialiser le formulaire
            contactForm.reset();

            // Réinitialiser le bouton après 3 secondes
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '';
                button.disabled = false;
            }, 3000);
        }, 1500);
    });
}

// ===================================
// Formulaire newsletter
// ===================================
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const button = newsletterForm.querySelector('button[type="submit"]');
        const originalText = button.textContent;

        if (!emailInput.value) {
            return;
        }

        button.textContent = 'Inscription...';
        button.disabled = true;

        // Simuler l'inscription (à remplacer par un vrai appel API)
        setTimeout(() => {
            button.textContent = '✓ Inscrit !';
            button.style.backgroundColor = '#10b981';

            // Réinitialiser le formulaire
            newsletterForm.reset();

            // Réinitialiser le bouton après 3 secondes
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '';
                button.disabled = false;
            }, 3000);
        }, 1500);
    });
}

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
