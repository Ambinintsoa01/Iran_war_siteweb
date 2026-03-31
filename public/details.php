<?php
require_once __DIR__ . '/../includes/front_helpers.php';

$pdo = getPDO();
$slug = trim($_GET['slug'] ?? '');
$article = $slug === '' ? null : fetchArticleBySlug($pdo, $slug);
$sections = $article ? fetchSections($pdo, $article['article_id']) : [];

if (!$article) {
    http_response_code(404);
    include __DIR__ . '/../includes/front_header.php';
    echo '<div class="container py-5 text-center"><h1>Article introuvable</h1><a href="'.frontUrl('').'">Retour à l\'accueil</a></div>';
    include __DIR__ . '/../includes/front_footer.php';
    exit;
}

$pageTitle = $article['titre'] . ' — Le Journal';
include __DIR__ . '/../includes/front_header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <header class="mb-5 text-center">
                <nav class="mb-3">
                    <span class="badge bg-primary text-uppercase px-3 py-2">
                        <?php echo h($article['categorie_nom']); ?>
                    </span>
                </nav>
                <h1 class="display-3 fw-bold mb-4" style="font-family: 'Playfair Display', serif;">
                    <?php echo renderHtml($article['titre']); ?>
                </h1>
                <p class="lead text-secondary mb-4 fs-4 fst-italic">
                    <?php echo renderHtml($article['resume']); ?>
                </p>
                <div class="border-top border-bottom py-3 d-flex justify-content-between align-items-center small text-muted text-uppercase fw-bold">
                    <span>Par La Rédaction</span>
                    <span>Publié le <?php echo formatDate($article['date_publication']); ?></span>
                </div>
            </header>

            <?php if ($article['image_principale']): ?>
                <figure class="mb-5">
                    <img src="<?php echo h(imageUrl($article['image_principale'])); ?>" 
                         class="img-fluid w-100 shadow-sm" 
                         style="max-height: 600px; object-fit: cover; border-radius: 4px;" alt="">
                </figure>
            <?php endif; ?>

            <article class="article-content" style="font-size: 1.2rem; line-height: 1.8; color: #333;">
                <?php foreach ($sections as $section): ?>
                    <section class="mb-5">
                        <?php if (!empty($section['sous_titre'])): ?>
                            <h2 class="h3 fw-bold mb-3 mt-5" style="font-family: 'Playfair Display', serif;">
                                <?php echo renderHtml($section['sous_titre']); ?>
                            </h2>
                        <?php endif; ?>

                        <div class="mb-4">
                            <?php echo renderHtml(nl2br($section['contenu'])); ?>
                        </div>

                        <?php if (!empty($section['images'])): ?>
                            <div class="row g-3 mb-4">
                                <?php 
                                $imgCount = count($section['images']);
                                // On adapte la taille des colonnes selon le nombre d'images
                                $colClass = ($imgCount === 1) ? 'col-12' : (($imgCount === 2) ? 'col-md-6' : 'col-md-4');
                                ?>
                                <?php foreach ($section['images'] as $image): ?>
                                    <?php $imgUrl = imageUrl($image['path'] ?? null); ?>
                                    <?php if ($imgUrl): ?>
                                        <div class="<?php echo $colClass; ?>">
                                            <figure class="m-0">
                                                <img src="<?php echo h($imgUrl); ?>" 
                                                     class="img-fluid w-100 shadow-sm transition-hover" 
                                                     style="aspect-ratio: 16/9; object-fit: cover; border-radius: 4px;" 
                                                     alt="<?php echo h($image['alt_image'] ?? ''); ?>">
                                                <?php if (!empty($image['alt_image'])): ?>
                                                    <figcaption class="small text-muted mt-2 text-center fst-italic">
                                                        <?php echo h($image['alt_image']); ?>
                                                    </figcaption>
                                                <?php endif; ?>
                                            </figure>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </article>

            <footer class="mt-5 pt-5 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?php echo frontUrl(''); ?>" class="btn btn-outline-dark px-4">
                        ← Retour à l'accueil
                    </a>
                    <div class="share-links">
                        <span class="small fw-bold text-uppercase me-2 text-muted">Partager :</span>
                        <button class="btn btn-sm btn-light border">FB</button>
                        <button class="btn btn-sm btn-light border">X</button>
                    </div>
                </div>
            </footer>

        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/front_footer.php'; ?>