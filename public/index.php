<?php
require_once __DIR__ . '/../includes/front_helpers.php';

$pdo = getPDO();
$categories = fetchCategories($pdo);
$currentCategory = trim($_GET['categorie'] ?? '');
$articles = fetchArticles($pdo, $currentCategory !== '' ? $currentCategory : null);
$pageTitle = 'The News — Iran War';
$pageDescription = 'Derniers articles et analyses sur la guerre Iran-Irak : contexte, chronologie, fronts et impact humain.';

include __DIR__ . '/../includes/front_header.php';
?>

<main class="container-fluid px-md-5 py-4">

    <?php if (empty($articles)): ?>
        <div class="alert alert-light text-center py-5 border">
            Aucun article disponible pour le moment.
        </div>
    <?php else: 
            $isFiltered = $currentCategory !== '';
            // Page d'accueil : un article "à la une" + les autres en grille
            // Filtre catégorie : pas de "à la une", tous les articles en grille
            if (!$isFiltered) {
                $featured = $articles[0] ?? null;
                $others = array_slice($articles, 1);
            }
        ?>

            <?php if (!$isFiltered && !empty($featured)): ?>
                <section class="row g-lg-5 align-items-center mb-5 pb-5 border-bottom">
                    <div class="col-lg-5 order-2 order-lg-1">
                        <div class="mb-2">
                            <span class="text-primary fw-bold text-uppercase small" style="letter-spacing: 1px;">
                                <?php echo h($featured['categorie_nom']); ?>
                            </span>
                        </div>
                        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif; line-height: 1.1;">
                            <a href="<?php echo frontUrl('article/' . h($featured['slug'])); ?>" class="text-decoration-none text-dark">
                                <?php echo renderHtml($featured['titre']); ?>
                            </a>
                        </h1>
                        <p class="lead text-muted mb-4">
                            <?php echo renderHtml($featured['resume']); ?>
                        </p>
                        <div class="d-flex align-items-center small text-muted">
                            <span class="fw-bold text-dark">Par La Rédaction</span>
                            <span class="mx-2">—</span>
                            <span><?php echo formatDate($featured['date_publication']); ?></span>
                        </div>
                    </div>

                    <div class="col-lg-7 order-1 order-lg-2 mb-4 mb-lg-0">
                        <a href="<?php echo frontUrl('article/' . h($featured['slug'])); ?>">
                            <img src="<?php echo h(imageUrl($featured['image_principale'])); ?>" 
                                 class="img-fluid shadow-sm w-100" 
                                 style="height: 550px; object-fit: cover;" 
                                 alt="<?php echo h($featured['titre']); ?>">
                        </a>
                    </div>
                </section>
            <?php endif; ?>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 border-bottom border-2 border-dark pb-2">
            <h2 class="fw-bold mb-3 mb-md-0" style="font-family: 'Playfair Display', serif;">Récemment ajoutés</h2>
            
            <nav class="nav">
                <a class="nav-link ps-0 py-1 text-uppercase fw-bold small <?php echo $currentCategory === '' ? 'text-dark border-bottom border-2 border-dark' : 'text-muted'; ?>" 
                   href="<?php echo frontUrl(''); ?>">Toutes</a>
                
                <?php foreach ($categories as $cat): ?>
                    <a class="nav-link py-1 text-uppercase fw-bold small <?php echo $currentCategory === $cat['slug_cat'] ? 'text-dark border-bottom border-2 border-dark' : 'text-muted'; ?>" 
                       href="<?php echo frontUrl('categorie/' . h($cat['slug_cat'])); ?>">
                        <?php echo h($cat['nom']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php 
            $gridArticles = $isFiltered ? $articles : ($others ?? []);
        ?>

        <section class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-5">
            <?php foreach ($gridArticles as $article): ?>
                <div class="col">
                    <article class="card h-100 border-0 bg-transparent">
                        <a href="<?php echo frontUrl('article/' . h($article['slug'])); ?>" class="text-decoration-none text-dark">
                            <div class="mb-3 overflow-hidden">
                                <img src="<?php echo h(imageUrl($article['image_principale'])); ?>" 
                                     class="img-fluid w-100 transition-hover" 
                                     style="aspect-ratio: 4/3; object-fit: cover;" 
                                     alt="<?php echo h($article['alt_img']); ?>">
                            </div>
                            <div class="card-body p-0">
                                <span class="text-primary fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">
                                    <?php echo h($article['categorie_nom']); ?>
                                </span>
                                <h3 class="h5 fw-bold mb-2" style="font-family: 'Playfair Display', serif; line-height: 1.3;">
                                    <?php echo renderHtml($article['titre']); ?>
                                </h3>
                                <div class="text-muted small">
                                    <?php echo formatDate($article['date_publication']); ?>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
            <?php endforeach; ?>
        </section>

    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/front_footer.php'; ?>