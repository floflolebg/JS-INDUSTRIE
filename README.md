# JS Industrie - Site Web Vitrine avec Blog

Site web professionnel pour JS Industrie, une entreprise spécialisée en chaudronnerie industrielle.

## 🚀 Structure du Projet

```
JS-INDUSTRIE/
│
├── index.html                          # Page d'accueil
├── blog.html                           # Page de liste des articles
├── README.md                           # Documentation
│
├── css/
│   └── style.css                       # Styles principaux
│
├── js/
│   └── main.js                         # Scripts JavaScript
│
├── images/                             # Dossier pour les images
│
└── blog/
    └── articles/
        └── normes-securite-2024.html   # Exemple d'article
```

## 📄 Pages Disponibles

### 1. Page d'Accueil (index.html)
- **Hero Section** : Présentation principale avec appel à l'action
- **Services** : 4 services principaux (Chaudronnerie, Tuyauterie, Structures, Finitions)
- **Réalisations** : Portfolio de 3 projets
- **À propos** : Présentation de l'entreprise avec statistiques
- **Blog Preview** : Aperçu des 3 derniers articles
- **Contact** : Formulaire de contact et coordonnées
- **Footer** : Navigation et informations

### 2. Page Blog (blog.html)
- Liste complète des articles
- Filtres par catégorie (Actualités, Projets, Technique, Sécurité)
- Pagination
- Newsletter
- Design cards moderne

### 3. Page Article (blog/articles/normes-securite-2024.html)
- Article complet avec contenu riche
- Sidebar avec articles similaires
- Breadcrumb navigation
- Métadonnées (auteur, date, temps de lecture)
- Call-to-action

## 🎨 Caractéristiques Design

### Palette de Couleurs
- **Primaire** : #1a365d (Bleu foncé - professionnalisme)
- **Secondaire** : #2563eb (Bleu vif - confiance)
- **Accent** : #f59e0b (Orange - énergie)
- **Texte** : #1f2937 / #6b7280
- **Fond** : #f9fafb / #ffffff

### Responsive Design
- **Desktop** : > 968px (version complète)
- **Tablette** : 768px - 968px (layout adapté)
- **Mobile** : < 768px (menu hamburger, colonnes simples)

## ⚙️ Fonctionnalités JavaScript

### Navigation
- Menu mobile avec animation hamburger
- Scroll smooth vers les sections
- Header fixe avec ombre au scroll

### Blog
- Filtres dynamiques par catégorie
- Pagination interactive
- Animations au scroll (Intersection Observer)

### Formulaires
- Validation côté client
- Messages de confirmation
- Design moderne et accessible

### Effets Visuels
- Animations au scroll pour les cards
- Compteur animé pour les statistiques
- Effet parallaxe léger sur le hero
- Transitions fluides

## 🛠️ Installation et Utilisation

1. **Cloner le repository**
```bash
git clone [URL_DU_REPO]
cd JS-INDUSTRIE
```

2. **Ouvrir dans un navigateur**
```bash
# Option 1 : Double-cliquer sur index.html

# Option 2 : Serveur local avec Python
python -m http.server 8000

# Option 3 : Serveur local avec Node.js
npx http-server
```

3. **Accéder au site**
- Ouvrir `http://localhost:8000` dans votre navigateur

## 📝 Personnalisation

### Modifier les Informations de Contact
Éditer les sections contact dans `index.html` et `blog.html` :
```html
<p>+33 X XX XX XX XX</p>
<p>contact@js-industrie.fr</p>
<p>Zone Industrielle<br>Code Postal Ville<br>France</p>
```

### Ajouter des Images
1. Placer vos images dans le dossier `images/`
2. Remplacer les backgrounds gradients par vos images :
```html
<!-- Avant -->
<div class="realisation-image" style="background: linear-gradient(...);">

<!-- Après -->
<div class="realisation-image" style="background-image: url('images/projet1.jpg');">
```

### Créer un Nouvel Article
1. Dupliquer `blog/articles/normes-securite-2024.html`
2. Modifier le contenu
3. Ajouter la référence dans `blog.html`

### Modifier les Couleurs
Éditer les variables CSS dans `css/style.css` :
```css
:root {
    --primary-color: #1a365d;
    --secondary-color: #2563eb;
    --accent-color: #f59e0b;
}
```

## 🔧 Améliorations Futures

### Backend
- [ ] Intégrer un CMS (WordPress, Strapi, etc.)
- [ ] Système d'envoi d'emails pour les formulaires
- [ ] Base de données pour les articles
- [ ] Panel d'administration

### Fonctionnalités
- [ ] Recherche d'articles
- [ ] Commentaires sur les articles
- [ ] Partage sur réseaux sociaux
- [ ] Mode sombre
- [ ] Multilingue (FR/EN)
- [ ] Système de tags
- [ ] Archives par date

### Performance
- [ ] Optimisation des images (WebP, lazy loading)
- [ ] Minification CSS/JS
- [ ] Service Worker pour PWA
- [ ] Mise en cache

### SEO
- [ ] Sitemap.xml
- [ ] Robots.txt
- [ ] Balises Open Graph
- [ ] Schema.org markup
- [ ] Google Analytics

## 📱 Compatibilité Navigateurs

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile Safari (iOS 12+)
- ✅ Chrome Mobile

## 📄 License

Ce projet est destiné à JS Industrie. Tous droits réservés.

## 👨‍💻 Support

Pour toute question ou demande de modification :
- Email : contact@js-industrie.fr
- Téléphone : +33 X XX XX XX XX

---

Développé avec ❤️ pour JS Industrie
