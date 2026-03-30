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
    return $text !== '' ? $text : 'article';
}

function generateId(string $prefix = 'ART'): string {
    try {
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
    } catch (Exception $e) {
        return $prefix . '-' . strtoupper(substr(uniqid('', true), -6));
    }
}

function generateDetailId(): string {
    return generateId('DET');
}

function generateImageId(): string {
    return generateId('IMG');
}

$pdo = getPDO();
$sidebarBaseUrl = '../';

$categoriesStmt = $pdo->query('SELECT categorie_id, nom FROM categorie ORDER BY nom ASC');
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$titre = '';
$slug = '';
$resume = '';
$image = '';
$idCategorie = '';
$sections = [[
    'sous_titre' => '',
    'contenu' => '',
    'image_alt' => ''
]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $resume = trim($_POST['resume'] ?? '');
    $image = '';
    $idCategorie = trim($_POST['id_categorie'] ?? '');
    $sousTitres = $_POST['sous_titre'] ?? [];
    $contenus = $_POST['contenu'] ?? [];
    $imageAlts = $_POST['section_image_alt'] ?? [];

    $sections = [];
    $countSections = max(count($sousTitres), count($contenus));
    for ($i = 0; $i < $countSections; $i++) {
        $sections[] = [
            'sous_titre' => trim($sousTitres[$i] ?? ''),
            'contenu' => trim($contenus[$i] ?? ''),
            'image_alt' => trim($imageAlts[$i] ?? '')
        ];
    }

    if ($titre === '') {
        $errors[] = 'Le titre est requis.';
    }

    if ($resume === '') {
        $errors[] = 'Le résumé est requis.';
    }

    if ($slug === '') {
        $slug = slugify($titre);
    } else {
        $slug = slugify($slug);
    }

    if ($slug === '') {
        $errors[] = 'Le slug ne peut pas être vide.';
    }

    if ($idCategorie === '') {
        $errors[] = 'La catégorie est requise.';
    }

    $hasSection = false;
    foreach ($sections as $section) {
        if ($section['sous_titre'] !== '' || $section['contenu'] !== '') {
            $hasSection = true;
            break;
        }
    }
    if (!$hasSection) {
        $errors[] = 'Ajoutez au moins une section (sous-titre et contenu).';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM article WHERE slug = ?');
    $stmt->execute([$slug]);
    if ((int) $stmt->fetchColumn() > 0) {
        $errors[] = 'Ce slug est déjà utilisé par un article.';
    }

    // Validation image principale upload
    if (!empty($_FILES['image_principale']['name'])) {
        if ($_FILES['image_principale']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload de l'image principale échoué.";
        }
    }

    if (empty($errors)) {
        try {
            $uploadDir = realpath(__DIR__ . '/../../uploads') ?: (__DIR__ . '/../../uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $pdo->beginTransaction();

            $articleId = generateId();
            // Upload image principale si fournie
            $imagePath = null;
            if (!empty($_FILES['image_principale']['name']) && $_FILES['image_principale']['error'] === UPLOAD_ERR_OK) {
                $tmpMain = $_FILES['image_principale']['tmp_name'];
                $origMain = $_FILES['image_principale']['name'];
                $extMain = pathinfo($origMain, PATHINFO_EXTENSION);
                $targetMain = 'main_' . uniqid() . ($extMain ? ('.' . $extMain) : '');
                $fullMain = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $targetMain;
                if (move_uploaded_file($tmpMain, $fullMain)) {
                    $imagePath = 'uploads/' . $targetMain;
                } else {
                    $errors[] = "Échec de l'upload de l'image principale.";
                }
            }

            $insert = $pdo->prepare('INSERT INTO article (article_id, titre, slug, resume, image_principale, id_categorie) VALUES (:id, :titre, :slug, :resume, :image, :categorie)');
            $insert->execute([
                ':id' => $articleId,
                ':titre' => $titre,
                ':slug' => $slug,
                ':resume' => $resume,
                ':image' => $imagePath,
                ':categorie' => $idCategorie
            ]);

            foreach ($sections as $index => $section) {
                if ($section['sous_titre'] === '' && $section['contenu'] === '') {
                    continue;
                }

                $detailId = generateDetailId();
                $insertDetail = $pdo->prepare('INSERT INTO article_details (details_id, article_id, sous_titre, contenu, slug_details) VALUES (:id, :article, :titre, :contenu, :slug)');
                $insertDetail->execute([
                    ':id' => $detailId,
                    ':article' => $articleId,
                    ':titre' => $section['sous_titre'],
                    ':contenu' => $section['contenu'],
                    ':slug' => slugify($section['sous_titre'] ?: ('section-' . ($index + 1)))
                ]);

                if (!empty($_FILES['section_image']['name'][$index]) && is_array($_FILES['section_image']['name'][$index])) {
                    foreach ($_FILES['section_image']['name'][$index] as $imgIdx => $nameImg) {
                        if ($nameImg === '') {
                            continue;
                        }
                        if ($_FILES['section_image']['error'][$index][$imgIdx] !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        $tmpName = $_FILES['section_image']['tmp_name'][$index][$imgIdx];
                        $ext = pathinfo($nameImg, PATHINFO_EXTENSION);
                        $targetName = 'img_' . uniqid() . ($ext ? ('.' . $ext) : '');
                        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $targetName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $imgId = generateImageId();
                            $alts = array_map('trim', explode(';', $section['image_alt'] ?? ''));
                            $altForImg = $alts[$imgIdx] ?? $section['image_alt'] ?? '';
                            $insertImg = $pdo->prepare('INSERT INTO image (image_id, details_id, path, alt_image) VALUES (:id, :detail, :path, :alt)');
                            $insertImg->execute([
                                ':id' => $imgId,
                                ':detail' => $detailId,
                                ':path' => 'uploads/' . $targetName,
                                ':alt' => $altForImg !== '' ? $altForImg : $section['sous_titre']
                            ]);
                        } else {
                            $errors[] = "Échec de l'upload d'une image de section.";
                        }
                    }
                }
            }

            $pdo->commit();
            header('Location: liste.php?success=created');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la sauvegarde : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un article - Backoffice</title>
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
                    <h1 class="mb-0">Nouvel article</h1>
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
                        <form method="post" novalidate enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug (optionnel)</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" placeholder="sera généré automatiquement">
                                <div class="form-text">Utilisé pour les URLs (/article/&lt;slug&gt;).</div>
                            </div>
                            <div class="mb-3">
                                <label for="resume" class="form-label">Résumé</label>
                                <textarea class="form-control" id="resume" name="resume" rows="3" required><?php echo htmlspecialchars($resume, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image_principale" class="form-label">Image principale (upload)</label>
                                <input type="file" class="form-control" id="image_principale" name="image_principale" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="id_categorie" class="form-label">Catégorie</label>
                                <select class="form-select" id="id_categorie" name="id_categorie" required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['categorie_id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $cat['categorie_id'] === $idCategorie ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Sections</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-section">Ajouter une section</button>
                            </div>
                            <div id="sections-wrapper">
                                <?php foreach ($sections as $idx => $section): ?>
                                    <div class="border rounded p-3 mb-3 section-block" data-index="<?php echo $idx; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Sous-titre</label>
                                            <input type="text" class="form-control" name="sous_titre[]" value="<?php echo htmlspecialchars($section['sous_titre'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contenu</label>
                                            <textarea class="form-control" name="contenu[]" rows="3"><?php echo htmlspecialchars($section['contenu'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Image de section (upload multiple)</label>
                                                <input type="file" class="form-control" name="section_image[<?php echo $idx; ?>][]" accept="image/*" multiple>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Textes alternatifs (séparez par ";")</label>
                                                <input type="text" class="form-control" name="section_image_alt[]" value="<?php echo htmlspecialchars($section['image_alt'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex : alt1; alt2; alt3 dans l'ordre des images">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
        function liveSlug(text) {
            return text
                .normalize('NFD')
                .replace(/\p{Diacritic}+/gu, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 100);
        }

        const titreInput = document.getElementById('titre');
        const slugInput = document.getElementById('slug');

        titreInput.addEventListener('input', () => {
            if (slugInput.value.trim() === '') {
                slugInput.value = liveSlug(titreInput.value);
            }
        });

        const wrapper = document.getElementById('sections-wrapper');
        const addBtn = document.getElementById('add-section');

        addBtn.addEventListener('click', () => {
            const idx = wrapper.querySelectorAll('.section-block').length;
            const block = document.createElement('div');
            block.className = 'border rounded p-3 mb-3 section-block';
            block.setAttribute('data-index', idx);
            block.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Section ${idx + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section">Supprimer</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sous-titre</label>
                    <input type="text" class="form-control" name="sous_titre[]">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea class="form-control" name="contenu[]" rows="3"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Image de section (upload multiple)</label>
                        <input type="file" class="form-control" name="section_image[${idx}][]" accept="image/*" multiple>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Textes alternatifs (séparez par ";")</label>
                        <input type="text" class="form-control" name="section_image_alt[]" placeholder="Ex : alt1; alt2; alt3 dans l'ordre des images">
                    </div>
                </div>
            `;
            wrapper.appendChild(block);
        });

        wrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-section')) {
                e.target.closest('.section-block').remove();
            }
        });
    </script>
</body>
</html>
