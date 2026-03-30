# TODO - Projet Site Web sur la Guerre en Iran

## Technologies utilisées
- HTML pour la structure et les vues
- PHP pour la logique serveur et les interactions avec la base de données
- MySQL pour la base de données

## 1. Conception et Mise en Place de la Base de Données
- Créer la base de données MySQL (nom : Iran_War)
- Créer la table `categorie` :
  - categorie_id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - nom (VARCHAR(255), NOT NULL)
  - slug_cat (VARCHAR(255), UNIQUE, NOT NULL)
- Créer la table `article` :
  - article_id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - titre (VARCHAR(255), NOT NULL)
  - slug (VARCHAR(255), UNIQUE, NOT NULL)
  - resume (TEXT, NOT NULL)  // Chapeau de l'article
  - image_principale (VARCHAR(255))  // Chemin vers l'image
  - date_publication (DATETIME, DEFAULT CURRENT_TIMESTAMP)
  - id_categorie (INT, FOREIGN KEY vers categorie.categorie_id)
  - meta_title (VARCHAR(60))  // Pour SEO
  - meta_description (VARCHAR(160))  // Pour SEO
- Créer la table `article_details` :
  - details_id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - article_id (INT, FOREIGN KEY vers article.article_id)
  - sous_titre (VARCHAR(255), NOT NULL)
  - contenu (TEXT, NOT NULL)
  - slug_details (VARCHAR(255), UNIQUE, NOT NULL)
- Créer la table `image` :
  - image_id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - details_id (INT, FOREIGN KEY vers article_details.details_id)
  - path (VARCHAR(255), NOT NULL)
  - alt_image (VARCHAR(255), NOT NULL)
- Créer la table `user` :
  - user_id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - email (VARCHAR(255), UNIQUE, NOT NULL)
  - password (VARCHAR(255), NOT NULL)  // Hashé avec password_hash()
- Insérer des données de test : Au moins 2-3 catégories, 5-10 articles avec détails et images
- Créer des triggers si nécessaire (fichier 29-03-2026-trigger.sql déjà présent)

## BACKOFFICE (Administration - Interface pour les administrateurs)

### Sécurité et Authentification
- Créer une page de login (login.php) :
  - Formulaire avec email et password
  - Vérification en PHP avec la table user
  - Session PHP pour maintenir la connexion
  - Redirection vers le dashboard après login
- Créer une page de logout (logout.php) :
  - Détruire la session et rediriger vers login
- Protéger toutes les pages backoffice avec une vérification de session

### Dashboard Administrateur
- Créer un dashboard (admin/dashboard.php) :
  - Menu de navigation vers CRUD articles, catégories, etc.
  - Affichage des statistiques (nombre d'articles, etc.)
  - Les menus sont visible dans le fichier bdd/29-03-2026/29-03-2026-menu.sql (sidebar)
    - level 1 : onglet
    - level 2 : option (liste/saisie)

### Gestion des Catégories
- Page liste catégories (admin/categorie/liste.php) : Afficher toutes les catégories
- Page ajouter catégorie (admin/categorie/saisie.php) : Formulaire pour créer une nouvelle catégorie (nom, génération automatique du slug)
- Page modifier catégorie (admin/categorie/edit.php) : Formulaire pré-rempli pour éditer
- Page supprimer catégorie (admin/categorie/delete.php) : Confirmation et suppression (attention aux articles liés)

### CRUD Articles
- Page liste articles (admin/article/articles.php) :
  - Afficher tous les articles avec titre, catégorie, date, actions (éditer, supprimer)
  - Pagination si nécessaire
- Page ajouter article (admin/article/saisie.php) :
  - Formulaire avec :
    - Titre (TinyMCE WYSIWYG riche - stocké avec balises HTML)
    - Résumé (TinyMCE WYSIWYG riche - stocké avec balises HTML)
    - Image principale (upload fichier)
    - Catégorie (select)
    - Meta title (champ pour SEO)
    - Meta description (champ pour SEO)
    - Sections : Possibilité d'ajouter plusieurs sections
      - Sous-titre (TinyMCE WYSIWYG riche - stocké avec balises HTML)
      - Contenu (TinyMCE WYSIWYG riche - stocké avec balises HTML)
      - Images associées (upload multiple, alt list)
  - Upload d'images pour les sections
  - Sauvegarde en base avec insertion dans article et article_details
- Page modifier article (admin/article/edit.php) :
  - Formulaire pré-rempli avec données existantes
  - Titre et Résumé avec TinyMCE (contenu HTML parsé et éditable)
  - Possibilité d'ajouter/modifier/supprimer des sections
  - Sous-titre et Contenu des sections avec TinyMCE (contenu HTML parsé et éditable)
  - Gestion des images (ajouter, remplacer, supprimer)
- Page supprimer article (admin/article/delete.php) :
  - Confirmation et suppression en cascade (article_details, images)

### Gestion des Utilisateurs
- (Optionnel) Page pour changer le mot de passe de l'admin

### Éditeur WYSIWYG (TinyMCE)
- Intégrer TinyMCE dans les formulaires pour les champs texte riche :
  - **Articles** : Titre et Résumé avec éditeur riche (balises HTML)
  - **Sections** : Sous-titre et Contenu avec éditeur riche (balises HTML)
- Les données saisies (avec balises HTML : `<h1>`, `<b>`, `<i>`, etc.) sont stockées directement en base
- À l'affichage, les balises sont conservées et rendues correctement (pas d'échappement)
- Fichiers CDN : https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js (pas de clé API nécessaire)
- Configuration TinyMCE :
  - Plugins : `link`, `image`, `lists`, `code`
  - Toolbar : `undo redo | blocks | bold italic underline | bullist numlist | link image | code`
  - block_formats : `Paragraphe=p;Titre 1=h1;Titre 2=h2;Titre 3=h3;Titre 4=h4;Citation=blockquote` pour choisir les balises de titre
  - Content-type : `application/json` pour les uploads images

### Preview
- Je devrais avoir un visuel reel de ce que le public peux voir
  - Les layouts (header, footer, sidebar), ainsi que le corps du site
  - header qui affichera les 3 tires de menu retractable et l'option accueil
- Une page qui affiche la page d'accueil publique (admin/preview/preview.php)
  - Afficher uniquement l'article avec son image
- Une page qui affiche les details de l'article que le client clic (admin/preview/details.php)
  - Afficher l'article et ses section (article_details) avec les images

## FRONTOFFICE (Partie Publique - Affichage pour les visiteurs)

### Structure Générale
- Créer un header commun (includes/header.php) avec un menu de navigation discret (ex: "Le journal", "Services", recherche, catégories via un menu "hamburger").
- Créer un footer commun (includes/footer.php) avec des informations (copyright, etc.).
- Créer un sidebar pour selectionner les categories
- Utiliser des templates PHP pour éviter la répétition.

### Page d'Accueil (Home - index.php)
- C'est le preview/preview du backoffice
- Afficher tous les articles publiés en pleine page (style "Le Monde").
  - Afficher le contenu complet de l'article :
    - Titre (h1)
    - Image principale
    - Résumé (chapeau)
    - Date de publication
    - Catégorie

### Page details (details.php)
- C'est le preview/detail du backoffice
- Afficher le contenu complet de l'article :
  - Titre (h1)
  - Image principale
  - Résumé (chapeau)
  - Date de publication
  - Catégorie
  - Sections : Pour chaque article_details, afficher sous_titre (h2), contenu, images associées avec alt.

### Gestion des Images
- Dossier uploads/ pour stocker les images.
- Affichage des images avec balises `<img>` et attributs alt corrects.

## 4. Optimisation SEO et Structure HTML
- Pour chaque page :
  - Balise `<title>` dynamique (< 60 caractères)
  - Meta description (< 160 caractères)
  - Hiérarchie de titres : h1 pour titre article, h2 pour sections, h3 pour sous-points
  - Attributs alt pour toutes les images
- Structure sémantique : Utiliser `<main>`, `<section>`, `<article>`, etc.

## 5. URL Rewriting
- Créer un fichier .htaccess à la racine :
  - RewriteEngine On
  - RewriteRule ^article/([a-zA-Z0-9-]+)$ article.php?slug=$1 [L]
  - RewriteRule ^categorie/([a-zA-Z0-9-]+)$ categorie.php?slug=$1 [L]
- Générer les slugs en PHP : Fonction pour transformer titre en slug (minuscules, pas d'accents, tirets)
- Liens dans le front : Utiliser les URLs propres (/article/slug au lieu de article.php?slug=...)

## 6. Tests et Validation
- Tester toutes les fonctionnalités CRUD
- Vérifier l'affichage front avec données de test
- Tests de performance avec Lighthouse (Chrome DevTools) :
  - Performance, Accessibilité, Best Practices, SEO
  - Viser score > 90
  - Optimiser images, temps de réponse
- Tests de sécurité : Injection SQL, XSS (échappement des données)
- Validation HTML/CSS

## 7. Déploiement (si nécessaire)
- Mettre en ligne sur un serveur avec PHP et MySQL
- Configurer la base de données en production