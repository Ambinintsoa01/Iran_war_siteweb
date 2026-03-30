<?php
require_once __DIR__ . '/../includes/front_helpers.php';

$pdo = getPDO();
$categories = fetchCategories($pdo);
$currentCategory = trim($_GET['categorie'] ?? '');
$articles = fetchArticles($pdo, $currentCategory !== '' ? $currentCategory : null);
$pageTitle = 'Accueil — Le Journal';

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
                <?php if (empty($articles)): ?>
                    <div class="empty">Aucun article disponible pour le moment.</div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <article class="article-card mb-4">
                            <?php $img = imageUrl($article['image_principale'] ?? null); ?>
                            <?php if ($img): ?>
                                <img src="<?php echo h($img); ?>" alt="<?php echo h($article['titre'] ?? ''); ?>">
                            <?php else: ?>
                                <div style="background: radial-gradient(circle at 20% 20%, #e2e8f0, #f8fafc); width: 100%; height: 100%;"></div>
                            <?php endif; ?>
                            <div class="copy">
                                <div class="kicker">
                                    <span><?php echo h($article['categorie_nom'] ?? 'Une'); ?></span>
                                    <small><?php echo formatDate($article['date_publication'] ?? ''); ?></small>
                                </div>
                                <h1 class="article-title"><?php echo renderHtml($article['titre'] ?? ''); ?></h1>
                                <div class="lead"><?php echo renderHtml($article['resume'] ?? ''); ?></div>
                                <div class="mt-3">
                                    <a class="btn btn-outline-primary" href="<?php echo frontUrl('details.php?slug=' . ($article['slug'] ?? '')); ?>">Lire</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>

<?php include __DIR__ . '/../includes/front_footer.php'; ?>
