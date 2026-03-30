<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/config.php';

$pdo = getPDO();
$sidebarBaseUrl = '../';

$categoriesStmt = $pdo->query('SELECT nom, slug_cat FROM categorie ORDER BY nom ASC LIMIT 6');
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$articleStmt = $pdo->query('SELECT a.*, c.nom AS categorie_nom, c.slug_cat FROM article a LEFT JOIN categorie c ON c.categorie_id = a.id_categorie ORDER BY a.date_publication DESC, a.article_id DESC');
$articles = $articleStmt->fetchAll(PDO::FETCH_ASSOC);

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function renderHtml(?string $value): string {
    return $value ?? '';
}


function formatDate($dateString) {
    if (!$dateString) {
        return '';
    }
    try {
        $dt = new DateTime($dateString);
        return $dt->format('d M Y');
    } catch (Exception $e) {
        return $dateString;
    }
}

function imageUrl(?string $path): ?string {
    if (!$path) {
        return null;
    }
    $clean = preg_replace('#^\.\./#', '', $path);
    $clean = ltrim($clean, '/\\');
    $clean = str_replace('\\', '/', $clean);
    return '../../' . $clean;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation - Accueil</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        body { background: #ffffff; color: #1a202c; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .news-shell { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem 1.1rem; box-shadow: 0 6px 18px rgba(15,23,42,0.08); margin-bottom: 1rem; }
        .top-nav-left { display: flex; align-items: center; gap: 0.9rem; }
        .top-nav .brand { font-weight: 700; color: #0f172a; letter-spacing: 0.04em; }
        .top-nav .menu { display: flex; gap: 1rem; }
        .top-nav .menu a { color: #475569; text-decoration: none; font-weight: 600; }
        .top-nav .menu a:hover { color: #0f172a; }
        .preview-layout { position: relative; min-height: 100%; }
        .preview-sidebar { position: absolute; top: 0; left: 0; bottom: 0; width: 240px; max-width: 80%; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem; box-shadow: 0 10px 24px rgba(15,23,42,0.06); transition: transform 0.2s ease, opacity 0.2s ease; z-index: 2; overflow: auto; }
        .preview-content { position: relative; z-index: 1; }
        .preview-layout.sidebar-hidden .preview-sidebar { transform: translateX(-110%); opacity: 0; pointer-events: none; }
        .preview-sidebar h3 { font-size: 1.05rem; margin-bottom: 0.75rem; color: #0f172a; }
        .preview-sidebar ul { list-style: none; padding: 0; margin: 0; }
        .preview-sidebar li { margin-bottom: 0.35rem; }
        .preview-sidebar a { color: #475569; text-decoration: none; font-weight: 600; }
        .preview-sidebar a:hover { color: #0f172a; }
        .menu-icon { border: 1px solid #e5e7eb; background: #ffffff; width: 42px; height: 42px; border-radius: 10px; display: flex; flex-direction: column; justify-content: center; gap: 6px; padding: 0 10px; box-shadow: 0 4px 12px rgba(15,23,42,0.08); cursor: pointer; }
        .menu-icon:hover { background: #f8fafc; }
        .menu-icon span { display: block; height: 2px; background: #0f172a; border-radius: 4px; transition: transform 0.2s ease, opacity 0.2s ease; }
        .menu-icon.active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .menu-icon.active span:nth-child(2) { opacity: 0; }
        .menu-icon.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        .hero { position: relative; border-radius: 14px; overflow: hidden; background: linear-gradient(135deg, #eef2ff, #e0f2fe); min-height: 320px; display: grid; grid-template-columns: 1.3fr 1fr; gap: 0; box-shadow: 0 20px 60px rgba(15,23,42,0.12); }
        .hero img { width: 100%; height: 100%; object-fit: cover; }
        .hero .overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.35) 45%, rgba(255,255,255,0) 75%); }
        .hero-copy { position: relative; padding: 2rem; z-index: 2; font-family: "Merriweather", "Times New Roman", serif; }
        .kicker { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.8rem; background: #fef2f2; border: 1px solid #fecdd3; border-radius: 999px; color: #991b1b; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; }
        .article-title { font-size: 2rem; margin: 1rem 0 0.5rem; line-height: 1.2; color: #0f172a; font-family: "Merriweather", "Times New Roman", serif; }
        .lead { font-size: 1.05rem; color: #334155; max-width: 52ch; font-family: "Merriweather", "Times New Roman", serif; }
        .empty-state { padding: 2rem; background: #ffffff; border-radius: 12px; border: 1px dashed #cbd5e1; color: #334155; text-align: center; }
        @media (max-width: 900px) {
            .preview-layout { grid-template-columns: 1fr; }
            .hero { grid-template-columns: 1fr; min-height: 0; }
            .hero img { height: 260px; }
            .hero .overlay { background: linear-gradient(180deg, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0) 65%); }
            .hero-copy { padding: 1.5rem; }
            .article-title { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content" style="background: #ffffff;">
            <header class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted">Prévisualisation</p>
                    <h1 class="mb-0">Accueil publique (aperçu)</h1>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="../dashboard.php">Retour dashboard</a>
                    <?php if (!empty($articles)): ?>
                        <a class="btn btn-primary" href="../../index.php" target="_blank">Ouvrir le site public</a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="news-shell">
                <div class="top-nav">
                    <div class="top-nav-left">
                        <button type="button" class="menu-icon" id="toggle-sidebar" aria-label="Basculer le menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                        <div class="brand">Le Journal</div>
                    </div>
                    <div class="menu">
                        <a href="../preview.php">Accueil</a>
                        <a href="#">Le journal</a>
                        <a href="#">Services</a>
                    </div>
                </div>

                <div class="preview-layout">
                    <aside class="preview-sidebar">
                        <h3>Catégories</h3>
                        <ul>
                            <?php foreach ($categories as $cat): ?>
                                <li><a href="#"><?php echo h($cat['nom']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                    <div class="preview-content">
                        <?php if (empty($articles)): ?>
                            <div class="empty-state">
                                <h4 class="mb-2">Aucun article publié</h4>
                                <p class="mb-0">Créez un article pour voir l'aperçu client.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($articles as $article): ?>
                                <div class="hero mb-4">
                                    <?php if ($article['image_principale']): ?>
                                        <img src="<?php echo h(imageUrl($article['image_principale'])); ?>" alt="<?php echo h($article['alt_img']); ?>">
                                    <?php else: ?>
                                        <div style="background: radial-gradient(circle at 20% 20%, #e2e8f0, #f8fafc); height: 100%; width: 100%;"></div>
                                    <?php endif; ?>
                                    <div class="overlay"></div>
                                    <div class="hero-copy">
                                        <div class="kicker">
                                            <span><?php echo h($article['categorie_nom'] ?: 'Une'); ?></span>
                                            <small><?php echo formatDate($article['date_publication'] ?? ''); ?></small>
                                        </div>
                                        <h1 class="article-title"><?php echo renderHtml($article['titre']); ?></h1>
                                        <div class="lead"><?php echo renderHtml($article['resume']); ?></div>
                                        <div class="mt-3">
                                            <a class="btn btn-outline-primary" href="details.php?slug=<?php echo h($article['slug']); ?>">Voir les détails</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-sidebar');
            const layout = document.querySelector('.preview-layout');

            if (toggleBtn && layout) {
                toggleBtn.addEventListener('click', function () {
                    layout.classList.toggle('sidebar-hidden');
                    toggleBtn.classList.toggle('active');
                    const hidden = layout.classList.contains('sidebar-hidden');
                    toggleBtn.setAttribute('aria-label', hidden ? 'Ouvrir le menu' : 'Masquer le menu');
                });
            }
        });
    </script>
</body>
</html>
