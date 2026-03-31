<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/config.php';

$pdo = getPDO();
$sidebarBaseUrl = '../';

$successKey = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';

$successMessages = [
    'created' => 'Article créé avec succès.',
    'updated' => 'Article mis à jour avec succès.',
    'deleted' => 'Article supprimé avec succès.'
];

$stmt = $pdo->query("SELECT a.article_id, a.titre, a.slug, a.date_publication, c.nom AS categorie_nom FROM article a LEFT JOIN Categorie c ON a.id_categorie = c.categorie_id ORDER BY a.date_publication DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Backoffice</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted">Gestion des articles</p>
                    <h1 class="mb-0">Articles</h1>
                </div>
                <a class="btn btn-primary" href="saisie.php">Nouvel article</a>
            </header>

            <?php if (!empty($successKey) && isset($successMessages[$successKey])): ?>
                <div class="alert alert-success mt-3"><?php echo $successMessages[$successKey]; ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <section class="mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Titre</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Catégorie</th>
                                <th scope="col">Publié le</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Aucun article pour le moment.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge bg-light text-dark">/<?php echo htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars($article['categorie_nom'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($article['date_publication'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?php echo $article['article_id']; ?>">Éditer</a>
                                            <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $article['article_id']; ?>">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
