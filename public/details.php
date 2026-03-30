<?php
require_once __DIR__ . '/../includes/front_helpers.php';

$pdo = getPDO();
$slug = trim($_GET['slug'] ?? '');
$article = $slug === '' ? null : fetchArticleBySlug($pdo, $slug);
$sections = $article ? fetchSections($pdo, $article['article_id']) : [];
$categories = fetchCategories($pdo);
$currentCategory = $article['slug_cat'] ?? '';

if (!$article) {
    http_response_code(404);
}

$pageTitle = $article ? ($article['titre'] . ' — Le Journal') : 'Article introuvable';
include __DIR__ . '/../includes/front_header.php';
?>

        <div class="layout">
            <aside class="sidebar">
                <h3>Catégories</h3>
                <nav>
                    <ul>
                        <li><a class="<?php echo $currentCategory === '' ? 'active' : ''; ?>" href="<?php echo frontUrl(''); ?>">Toutes</a></li>
                        <?php foreach ($categories as $cat): ?>
                            <li><a class="<?php echo $currentCategory === $cat['slug_cat'] ? 'active' : ''; ?>" href="<?php echo frontUrl('index.php?categorie=' . h($cat['slug_cat'])); ?>"><?php echo h($cat['nom']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </aside>

            <main>
                <?php if (!$article): ?>
                    <div class="not-found">Article introuvable.</div>
                <?php else: ?>
                    <section class="hero">
                        <?php $hero = imageUrl($article['image_principale'] ?? null); ?>
                        <?php if ($hero): ?>
                            <img src="<?php echo h($hero); ?>" alt="<?php echo h($article['titre'] ?? ''); ?>">
                        <?php endif; ?>
                        <div class="overlay"></div>
                        <div class="content">
                            <div class="kicker">
                                <span><?php echo h($article['categorie_nom'] ?? 'Une'); ?></span>
                                <small><?php echo formatDate($article['date_publication'] ?? ''); ?></small>
                            </div>
                            <h1 class="article-title"><?php echo renderHtml($article['titre'] ?? ''); ?></h1>
                            <div class="lead"><?php echo renderHtml($article['resume'] ?? ''); ?></div>
                        </div>
                    </section>

                    <?php foreach ($sections as $section): ?>
                        <section class="section">
                            <?php if (!empty($section['sous_titre'])): ?>
                                <h2><?php echo renderHtml($section['sous_titre']); ?></h2>
                            <?php endif; ?>
                            <p><?php echo renderHtml(nl2br($section['contenu'])); ?></p>
                            <?php if (!empty($section['images'])): ?>
                                <div class="gallery">
                                    <?php foreach ($section['images'] as $image): ?>
                                        <?php $img = imageUrl($image['path'] ?? null); ?>
                                        <?php if ($img): ?>
                                            <img src="<?php echo h($img); ?>" alt="<?php echo h($image['alt_image'] ?? ''); ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>

<?php include __DIR__ . '/../includes/front_footer.php'; ?>
