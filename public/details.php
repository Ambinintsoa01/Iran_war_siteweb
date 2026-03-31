<?php
require_once __DIR__ . '/../includes/front_helpers.php';

$pdo = getPDO();
$slug = trim($_GET['slug'] ?? '');
$article = $slug === '' ? null : fetchArticleBySlug($pdo, $slug);
$sections = $article ? fetchSections($pdo, $article['article_id']) : [];

// On récupère d'autres articles pour la barre latérale (ex: les 5 derniers sauf l'actuel)
$stmt = $pdo->prepare("SELECT * FROM article WHERE slug != ? ORDER BY date_publication DESC LIMIT 5");
$stmt->execute([$slug]);
$sidebarArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$article) {
    http_response_code(404);
    include __DIR__ . '/../includes/front_header.php';
    echo '<div class="container py-5 text-center"><h1>Article introuvable</h1><a href="'.frontUrl('').'">Retour à l\'accueil</a></div>';
    include __DIR__ . '/../includes/front_footer.php';
    exit;
}

$pageTitle = $article['titre'] . ' — Le Journal';
$pageDescription = $article['resume'] ?? '';
include __DIR__ . '/../includes/front_header.php';
?>

<main class="container-fluid px-md-5 py-5">
    <div class="row g-5">
        
        <div class="col-lg-8 border-end">
            <header class="mb-5">
                <nav class="mb-3">
                    <span class="badge-cat">
                        <?php echo h($article['categorie_nom']); ?>
                    </span>
                </nav>
                <h1 class="display-4 fw-bold mb-4" style="font-family: 'Playfair Display', serif;">
                    <?php echo renderHtml($article['titre']); ?>
                </h1>
                <p class="lead mb-4 fs-4 fst-italic">
                    <?php echo renderHtml($article['resume']); ?>
                </p>
                <div class="border-top border-bottom py-3 d-flex justify-content-between align-items-center small text-muted text-uppercase fw-bold">
                    <span>Par La Rédaction</span>
                    <span><?php echo formatDate($article['date_publication']); ?></span>
                </div>
            </header>

            <?php if ($article['image_principale']): ?>
                <div class="img-wrapper mb-5">
                    <img src="<?php echo h(imageUrl($article['image_principale'])); ?>" 
                         class="img-fluid w-100 shadow-sm" 
                         alt="<?php echo h($article['alt_img']); ?>"
                         width="900" height="600">
                </div>
            <?php endif; ?>

            <article class="article-content" style="font-size: 1.15rem; line-height: 1.8;">
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
                                $colClass = ($imgCount === 1) ? 'col-12' : 'col-md-6';
                                ?>
                                <?php foreach ($section['images'] as $image): ?>
                                    <div class="<?php echo $colClass; ?>">
                                        <div class="img-wrapper">
                                            <img src="<?php echo h(imageUrl($image['path'])); ?>" class="img-fluid w-100" loading="lazy" decoding="async" alt="<?php echo h($image['alt_image'] ?? ''); ?>" width="800" height="600">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </article>
        </div>

        <aside class="col-lg-4">
            <div class="sticky-top" style="top: 2rem;">
                <h4 class="fw-bold mb-4 pb-2 border-bottom border-2 border-dark" style="font-family: 'Playfair Display', serif;">
                    À lire aussi
                </h4>
                
                <?php foreach ($sidebarArticles as $sideItem): ?>
                    <div class="sidebar-item mb-3">
                        <a href="<?php echo frontUrl('article/' . h($sideItem['slug'])); ?>" class="text-decoration-none text-dark">
                            <div class="row g-0 align-items-center">
                                <div class="col-4">
                                    <div class="img-wrapper" style="aspect-ratio: 1/1;">
                                        <img src="<?php echo h(imageUrl($sideItem['image_principale'])); ?>" 
                                            class="img-fluid h-100 w-100" 
                                            style="object-fit: cover;" loading="lazy" decoding="async" alt="<?php echo h($sideItem['alt_img'] ?? ''); ?>" width="424" height="282">
                                    </div>
                                </div>
                                <div class="col-8 ps-3">
                                    <h5 class="sidebar-title fw-bold mb-1" style="font-family: 'Playfair Display', serif; font-size: 0.9rem; line-height: 1.3; transition: 0.3s;">
                                        <?php echo renderHtml($sideItem['titre']); ?>
                                    </h5>
                                    <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">
                                        <?php echo formatDate($sideItem['date_publication']); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

            
            </div>
        </aside>

    </div>
</main>

<?php include __DIR__ . '/../includes/front_footer.php'; ?>