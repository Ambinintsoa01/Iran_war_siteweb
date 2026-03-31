<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/config.php';

$pdo = getPDO();
$sidebarBaseUrl = '../';

$rawId = trim($_GET['id'] ?? '');

function fetchArticle(PDO $pdo, string $rawId): ?array {
    if ($rawId === '') {
        return null;
    }

    if (preg_match('/^ART-(.+)$/i', $rawId)) {
        $stmt = $pdo->prepare('SELECT * FROM article WHERE article_id = ?');
        $stmt->execute([$rawId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }

    if (ctype_digit($rawId)) {
        $stmt = $pdo->prepare('SELECT * FROM article WHERE article_id = ?');
        $stmt->execute([$rawId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }

    $stmt = $pdo->prepare('SELECT * FROM article WHERE slug = ?');
    $stmt->execute([$rawId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

$article = fetchArticle($pdo, $rawId);
if (!$article) {
    header('Location: liste.php?error=Article%20introuvable');
    exit();
}
$articleId = $article['article_id'];

// Comptages pour info
$detailsStmt = $pdo->prepare('SELECT details_id FROM article_details WHERE article_id = ?');
$detailsStmt->execute([$articleId]);
$detailsIds = $detailsStmt->fetchAll(PDO::FETCH_COLUMN);
$detailsCount = count($detailsIds);

$imageCount = 0;
if ($detailsCount > 0) {
    $placeholders = implode(',', array_fill(0, $detailsCount, '?'));
    $imgStmt = $pdo->prepare('SELECT COUNT(*) FROM image WHERE details_id IN (' . $placeholders . ')');
    $imgStmt->execute($detailsIds);
    $imageCount = (int) $imgStmt->fetchColumn();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if ($detailsCount > 0) {
            $placeholders = implode(',', array_fill(0, $detailsCount, '?'));
            $delImg = $pdo->prepare('DELETE FROM image WHERE details_id IN (' . $placeholders . ')');
            $delImg->execute($detailsIds);

            $delDetails = $pdo->prepare('DELETE FROM article_details WHERE details_id IN (' . $placeholders . ')');
            $delDetails->execute($detailsIds);
        }

        $delArticle = $pdo->prepare('DELETE FROM article WHERE article_id = ?');
        $delArticle->execute([$articleId]);

        $pdo->commit();
        header('Location: liste.php?success=deleted');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Suppression impossible : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer l'article - Backoffice</title>
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
                    <h1 class="mb-0">Supprimer l'article</h1>
                </div>
                <a class="btn btn-outline-secondary" href="liste.php">Retour à la liste</a>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <section class="mt-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="card-title">Confirmer la suppression</h5>
                        <p class="card-text">Vous êtes sur le point de supprimer l'article <strong><?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
                        <p class="card-text"><span class="badge bg-light text-dark">Slug : /<?php echo htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="card-text text-warning">Sections liées : <?php echo $detailsCount; ?> | Images liées : <?php echo $imageCount; ?></p>

                        <form method="post" class="d-flex justify-content-end gap-2 mb-0">
                            <a class="btn btn-outline-secondary" href="liste.php">Annuler</a>
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
