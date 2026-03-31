<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/config.php';

$pdo = getPDO();
$sidebarBaseUrl = '../';

$categorieId = isset($_GET['id']) ? $_GET['id'] : '';
if ($categorieId === '') {
    header('Location: liste.php?error=Cat%C3%A9gorie%20introuvable');
    exit();
}

$stmt = $pdo->prepare('SELECT categorie_id, nom, slug_cat FROM categorie WHERE categorie_id = ?');
$stmt->execute([$categorieId]);
$categorie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categorie) {
    header('Location: liste.php?error=Cat%C3%A9gorie%20introuvable');
    exit();
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM article WHERE id_categorie = ?');
$countStmt->execute([$categorieId]);
$articleCount = (int) $countStmt->fetchColumn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($articleCount > 0) {
        $errors[] = 'Impossible de supprimer : des articles sont liés à cette catégorie.';
    } else {
        $delete = $pdo->prepare('DELETE FROM categorie WHERE categorie_id = ?');
        $delete->execute([$categorieId]);
        header('Location: liste.php?success=deleted');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer la catégorie - Backoffice</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted">Gestion des catégories</p>
                    <h1 class="mb-0">Supprimer la catégorie</h1>
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
                        <p class="card-text">Vous êtes sur le point de supprimer la catégorie <strong><?php echo htmlspecialchars($categorie['nom'], ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
                        <p class="card-text"><span class="badge bg-light text-dark">Slug : /<?php echo htmlspecialchars($categorie['slug_cat'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="card-text text-warning">Articles liés : <?php echo $articleCount; ?></p>

                        <?php if ($articleCount > 0): ?>
                            <div class="alert alert-warning">Supprimez ou réassignez les articles avant de supprimer cette catégorie.</div>
                        <?php endif; ?>

                        <form method="post" class="d-flex justify-content-end gap-2 mb-0">
                            <a class="btn btn-outline-secondary" href="liste.php">Annuler</a>
                            <button type="submit" class="btn btn-danger" <?php echo $articleCount > 0 ? 'disabled' : ''; ?>>Supprimer</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
