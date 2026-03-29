Ce mini-projet est un excellent exercice pour maîtriser le cycle complet d'un site **CMS (Content Management System)** tout en mettant l'accent sur le **SEO technique**. 

Voici une décomposition détaillée des étapes à suivre pour réussir ce projet d'ici la fin du mois.

---

## 1. Conception de la Base de Données
C'est le cœur de votre backoffice. Pour un site d'information, vous avez besoin d'une structure flexible.

* **Table `articles` :** `id`, `titre`, `slug` (pour l'URL rewriting), `chapeau` (résumé), `contenu` (corps du texte), `date_publication`, `image_principale`, `alt_image`, `id_categorie`.
* **Table `categories` :** `id`, `nom`, `description`.
* **Table `utilisateurs` :** Pour l'accès sécurisé au backoffice.

---

## 2. Développement du Backoffice (Administration)
C’est l’interface qui vous permettra de remplir le site sans toucher au code.
* **Sécurité :** Créez une page de login.
* **CRUD Articles :** Interface pour **C**réer, **R**ire (afficher), **U**pdate (modifier) et **D**elete (supprimer) les articles.
* **Gestion SEO :** Dans le formulaire d'ajout d'article, prévoyez des champs pour la balise `<title>` et la `meta-description` spécifique à chaque page.

---

## 3. Développement du FrontOffice (Affichage)
C'est la partie publique. Vous devez créer au minimum deux types de vues :
* **La Liste (Home) :** Affiche les derniers articles sous forme de cartes (titre + résumé + image).
* **L'Article (Détail) :** Affiche le contenu complet. C’est ici que la structure HTML est cruciale.

---

## 4. Optimisation SEO & Structure HTML
Le sujet porte sur la guerre en Iran ; la structure doit aider les moteurs de recherche à comprendre l'importance des informations.

* **Hiérarchie des titres :** * `<h1>` : Le titre unique de l'article (ex: "Chronologie du conflit...").
    * `<h2>` : Les grandes sections.
    * `<h3>` : Les sous-points.
    * *Interdit :* Ne sautez jamais de niveau (ne pas passer de h2 à h4).
* **Attributs Alt :** Chaque image doit avoir un `alt="Description précise de l'image"`. C'est essentiel pour l'accessibilité et Google Images.
* **Balises Meta :**
    ```html
    <title>Titre accrocheur de moins de 60 caractères</title>
    <meta name="description" content="Résumé de la page de 150-160 caractères pour inciter au clic.">
    ```

---

## 5. URL Rewriting (La "Normalisation")
C'est l'un des points techniques clés du projet. Vous devez passer d'URLs techniques à des URLs "propres".

* **Mauvais :** `article.php?id=12`
* **Bon :** `/conflit-iran-2026-comprendre-les-enjeux`

**Comment faire ?**
1.  **En PHP/Apache :** Utilisez un fichier `.htaccess` avec `RewriteRule`.
2.  **En Node.js/Express :** Définissez vos routes avec des paramètres nommés (ex: `app.get('/article/:slug', ...)`).
3.  **Stockage :** Le "slug" doit être généré à partir du titre lors de l'enregistrement en base de données (minuscules, pas d'accents, tirets à la place des espaces).

---

## 6. Tests de Performance (Lighthouse)
Une fois le site en ligne (ou en local), ouvrez l'inspecteur Chrome (F12) > Onglet **Lighthouse**.
* **Mode :** Testez "Navigation" pour Mobile ET Desktop.
* **Objectif :** Visez le score vert (90+) sur les 4 piliers : Performance, Accessibilité, Best Practices, et **SEO**.
* **Points de vigilance :** Poids des images, temps de réponse du serveur et contrastes de couleurs.



---

### Prochaines étapes suggérées
Souhaitez-vous que je vous aide à générer le fichier **.htaccess** pour l'URL rewriting ou que je vous propose un **schéma SQL** complet pour votre base de données ?