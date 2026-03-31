<?php
session_start();

// Vérification de la session
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
    'created' => 'Catégorie créée avec succès.',
    'updated' => 'Catégorie mise à jour avec succès.',
    'deleted' => 'Catégorie supprimée avec succès.'
];

$stmt = $pdo->query('SELECT categorie_id, nom, slug_cat FROM Categorie ORDER BY nom ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - Backoffice</title>
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
                    <h1 class="mb-0">Catégories</h1>
                </div>
                <a class="btn btn-primary" href="saisie.php">Nouvelle catégorie</a>
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
                                <th scope="col">Nom</th>
                                <th scope="col">Slug</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucune catégorie pour le moment.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $categorie): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($categorie['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge bg-light text-dark">/<?php echo htmlspecialchars($categorie['slug_cat'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?php echo $categorie['categorie_id']; ?>">Éditer</a>
                                            <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $categorie['categorie_id']; ?>">Supprimer</a>
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


