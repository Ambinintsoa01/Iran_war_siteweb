<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Inclusion du fichier de configuration
require_once '../includes/config.php';

// Connexion à la base de données
$pdo = getPDO();

// Récupération des statistiques
$stmt = $pdo->query("SELECT COUNT(*) as total_articles FROM article");
$total_articles = $stmt->fetch()['total_articles'];

$stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categorie");
$total_categories = $stmt->fetch()['total_categories'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM user");
$total_users = $stmt->fetch()['total_users'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - Iran War Website</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css"> <!-- À créer si nécessaire -->
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header>
                <h1>Dashboard Administrateur</h1>
            </header>
            <section class="stats">
                <div class="stat-card">
                    <h3>Total Articles</h3>
                    <p><?php echo $total_articles; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Catégories</h3>
                    <p><?php echo $total_categories; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Utilisateurs</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
            </section>
        </main>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>