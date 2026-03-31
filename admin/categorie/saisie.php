<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/config.php';

function slugify($text) {
    $text = trim($text);
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($transliterated !== false) {
        $text = $transliterated;
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'categorie';
}

$pdo = getPDO();
$sidebarBaseUrl = '../';

$errors = [];
$nom = '';
$slug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($nom === '') {
        $errors[] = 'Le nom de la catégorie est requis.';
    }

    if ($slug === '') {
        $slug = slugify($nom);
    } else {
        $slug = slugify($slug);
    }

    if ($slug === '') {
        $errors[] = 'Le slug ne peut pas être vide.';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Categorie WHERE slug_cat = ?');
    $stmt->execute([$slug]);
    $existing = (int) $stmt->fetchColumn();
    if ($existing > 0) {
        $errors[] = 'Ce slug existe déjà, merci d\'en choisir un autre.';
    }

    if (empty($errors)) {
        $insert = $pdo->prepare('INSERT INTO Categorie (nom, slug_cat) VALUES (:nom, :slug)');
        $insert->execute([
            ':nom' => $nom,
            ':slug' => $slug
        ]);

        header('Location: liste.php?success=created');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une catégorie - Backoffice</title>
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
                    <h1 class="mb-0">Nouvelle catégorie</h1>
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
                <div class="card">
                    <div class="card-body">
                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom de la catégorie</label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug (optionnel)</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" placeholder="sera généré automatiquement">
                                <div class="form-text">Utilisé pour les URLs (/categorie/&lt;slug&gt;).</div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-outline-secondary" href="liste.php">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Génère un slug en direct pour aider la saisie
        function liveSlug(text) {
            return text
                .normalize('NFD')
                .replace(/\p{Diacritic}+/gu, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 100);
        }

        const nomInput = document.getElementById('nom');
        const slugInput = document.getElementById('slug');

        nomInput.addEventListener('input', () => {
            if (slugInput.value.trim() === '') {
                slugInput.value = liveSlug(nomInput.value);
            }
        });
    </script>
</body>
</html>
