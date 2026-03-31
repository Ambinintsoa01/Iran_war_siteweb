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

$categoriesStmt = $pdo->query('SELECT categorie_id, nom FROM Categorie ORDER BY nom ASC');
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

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

$errors = [];
$titre = $article['titre'];
$slug = $article['slug'];
$resume = $article['resume'];
$image = $article['image_principale'];
$imageAlt = $article['alt_image'] ?? '';
$idCategorie = $article['id_categorie'];

$detailsStmt = $pdo->prepare('SELECT details_id, sous_titre, contenu, slug_details FROM article_details WHERE article_id = ? ORDER BY details_id');
$detailsStmt->execute([$articleId]);
$details = $detailsStmt->fetchAll(PDO::FETCH_ASSOC);

$sections = [];
$detailIdsList = [];
foreach ($details as $detail) {
    $sections[] = array_merge($detail, ['images' => []]);
    $detailIdsList[] = $detail['details_id'];
}

$existingImagesByDetail = [];
if (!empty($detailIdsList)) {
    $placeholders = implode(',', array_fill(0, count($detailIdsList), '?'));
    $imgStmt = $pdo->prepare("SELECT image_id, details_id, path, alt_image FROM image WHERE details_id IN ($placeholders) ORDER BY image_id");
    $imgStmt->execute($detailIdsList);
    while ($img = $imgStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingImagesByDetail[$img['details_id']][] = $img;
    }
    foreach ($sections as &$section) {
        $section['images'] = $existingImagesByDetail[$section['details_id']] ?? [];
    }
    unset($section);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer les contenus TinyMCE : supprimer les <p> wrapper inutiles
    $titre = $_POST['titre'] ?? '';
    if (!empty($titre)) {
        $titre = trim($titre);
        if (preg_match('/^<p>(.*)<\/p>$/s', $titre, $m)) {
            $titre = $m[1];
        }
    }
    $resume = $_POST['resume'] ?? '';
    if (!empty($resume)) {
        $resume = trim($resume);
        if (preg_match('/^<p>(.*)<\/p>$/s', $resume, $m)) {
            $resume = $m[1];
        }
    }
    $slug = trim($_POST['slug'] ?? '');
    $image = trim($_POST['image_principale'] ?? '');
    $imageAlt = trim($_POST['image_alt'] ?? '');
    $idCategorie = trim($_POST['id_categorie'] ?? '');
    $detailIds = $_POST['detail_id'] ?? [];
    $sousTitres = $_POST['sous_titre'] ?? [];
    $contenus = $_POST['contenu'] ?? [];
    $imageAlts = $_POST['section_image_alt'] ?? [];
    $removeIds = $_POST['section_remove'] ?? [];

    $titrePlain = trim(strip_tags($titre));
    $resumePlain = trim(strip_tags($resume));

    if ($titrePlain === '') {
        $errors[] = 'Le titre est requis.';
    }

    if ($resumePlain === '') {
        $errors[] = 'Le résumé est requis.';
    }

    if ($slug === '') {
        $slug = slugify($titrePlain ?: $titre);
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
    foreach ($sousTitres as $idx => $st) {
        $st = $st ?? '';
        $ct = $contenus[$idx] ?? '';
        $removed = in_array($detailIds[$idx] ?? '', $removeIds, true);
        if (!$removed && (trim(strip_tags($st)) !== '' || trim(strip_tags($ct)) !== '')) {
            $hasSection = true;
            break;
        }
    }
    if (!$hasSection) {
        $errors[] = 'Ajoutez au moins une section (sous-titre et contenu).';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM article WHERE slug = ? AND article_id <> ?');
    $stmt->execute([$slug, $articleId]);
    if ((int) $stmt->fetchColumn() > 0) {
        $errors[] = 'Ce slug est déjà utilisé par un autre article.';
    }

    if (empty($errors)) {
        try {
            $uploadDir = realpath(__DIR__ . '/../../uploads') ?: (__DIR__ . '/../../uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $pdo->beginTransaction();

            $update = $pdo->prepare('UPDATE article SET titre = :titre, slug = :slug, resume = :resume, image_principale = :image, alt_img = :alt, id_categorie = :categorie WHERE article_id = :id');
            $update->execute([
                ':titre' => $titre,
                ':slug' => $slug,
                ':resume' => $resume,
                ':image' => $image,
                ':alt' => $imageAlt,
                ':categorie' => $idCategorie,
                ':id' => $articleId
            ]);

            $filesSection = $_FILES['section_image']['name'] ?? [];
            $countSections = max(count($detailIds), count($sousTitres), count($contenus), count($imageAlts), count($filesSection));
            for ($i = 0; $i < $countSections; $i++) {
                $detailId = trim($detailIds[$i] ?? '');
                $sousTitre = $sousTitres[$i] ?? '';
                $contenu = $contenus[$i] ?? '';
                $altRaw = $imageAlts[$i] ?? '';
                $altList = array_map('trim', explode(';', $altRaw));
                $existingImages = $detailId !== '' ? ($existingImagesByDetail[$detailId] ?? []) : [];
                $remove = in_array($detailId, $removeIds, true);

                if ($remove && $detailId !== '') {
                    $pdo->prepare('DELETE FROM image WHERE details_id = ?')->execute([$detailId]);
                    $pdo->prepare('DELETE FROM article_details WHERE details_id = ?')->execute([$detailId]);
                    continue;
                }

                if ($detailId === '') {
                    if (trim(strip_tags($sousTitre)) === '' && trim(strip_tags($contenu)) === '') {
                        continue;
                    }
                    $detailId = generateDetailId();
                    $pdo->prepare('INSERT INTO article_details (details_id, article_id, sous_titre, contenu, slug_details) VALUES (:id, :article, :titre, :contenu, :slug)')->execute([
                        ':id' => $detailId,
                        ':article' => $articleId,
                        ':titre' => $sousTitre,
                        ':contenu' => $contenu,
                        ':slug' => slugify(trim(strip_tags($sousTitre)) ?: ('section-' . ($i + 1)))
                    ]);
                } else {
                    $pdo->prepare('UPDATE article_details SET sous_titre = :titre, contenu = :contenu, slug_details = :slug WHERE details_id = :id')->execute([
                        ':titre' => $sousTitre,
                        ':contenu' => $contenu,
                        ':slug' => slugify(trim(strip_tags($sousTitre)) ?: ('section-' . ($i + 1))),
                        ':id' => $detailId
                    ]);
                }

                // Met à jour les alts des images existantes selon l'ordre courant
                foreach ($existingImages as $imgIndex => $imgData) {
                    $newAlt = $altList[$imgIndex] ?? '';
                    if ($newAlt !== '') {
                        $pdo->prepare('UPDATE image SET alt_image = :alt WHERE image_id = :id')->execute([
                            ':alt' => $newAlt,
                            ':id' => $imgData['image_id']
                        ]);
                    }
                }

                // Ajoute les nouvelles images (upload multiple)
                if (isset($_FILES['section_image']['name'][$i]) && is_array($_FILES['section_image']['name'][$i])) {
                    $offsetAlt = count($existingImages);
                    foreach ($_FILES['section_image']['name'][$i] as $imgIdx => $nameImg) {
                        if ($nameImg === '') {
                            continue;
                        }
                        if ($_FILES['section_image']['error'][$i][$imgIdx] !== UPLOAD_ERR_OK) {
                            $errors[] = "Échec de l'upload d'une image de section.";
                            continue;
                        }
                        $tmpName = $_FILES['section_image']['tmp_name'][$i][$imgIdx];
                        $ext = pathinfo($nameImg, PATHINFO_EXTENSION);
                        $targetName = 'img_' . uniqid() . ($ext ? ('.' . $ext) : '');
                        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $targetName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $altForImg = $altList[$offsetAlt + $imgIdx] ?? '';
                            $pdo->prepare('INSERT INTO image (image_id, details_id, path, alt_image) VALUES (:id, :detail, :path, :alt)')->execute([
                                ':id' => generateImageId(),
                                ':detail' => $detailId,
                                ':path' => 'uploads/' . $targetName,
                                ':alt' => $altForImg !== '' ? $altForImg : $sousTitre
                            ]);
                        } else {
                            $errors[] = "Échec de l'upload d'une image de section.";
                        }
                    }
                }
            }

            $pdo->commit();
            header('Location: liste.php?success=updated');
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
    <title>Éditer l'article - Backoffice</title>
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
                    <h1 class="mb-0">Éditer l'article</h1>
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
                                <textarea class="form-control wysiwyg" id="titre" name="titre" rows="2" required><?php echo $titre; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" required>
                                <div class="form-text">Utilisé pour les URLs (/article/&lt;slug&gt;).</div>
                            </div>
                            <div class="mb-3">
                                <label for="resume" class="form-label">Résumé</label>
                                <textarea class="form-control wysiwyg" id="resume" name="resume" rows="4" required><?php echo $resume; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image_principale" class="form-label">Image principale (chemin)</label>
                                <input type="text" class="form-control" id="image_principale" name="image_principale" value="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="image_alt" class="form-label">Texte alternatif de l'image principale</label>
                                <input type="text" class="form-control" id="image_alt" name="image_alt" value="<?php echo htmlspecialchars($imageAlt, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Décrivez brièvement l'image">
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
                                <?php if (!empty($sections)): ?>
                                    <?php foreach ($sections as $idx => $section): ?>
                                        <div class="border rounded p-3 mb-3 section-block" data-index="<?php echo $idx; ?>">
                                            <input type="hidden" name="detail_id[]" value="<?php echo htmlspecialchars($section['details_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Section <?php echo $idx + 1; ?></h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-section" data-detail="<?php echo htmlspecialchars($section['details_id'], ENT_QUOTES, 'UTF-8'); ?>">Supprimer</button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Sous-titre</label>
                                                <textarea class="form-control wysiwyg" name="sous_titre[]" rows="2"><?php echo $section['sous_titre']; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Contenu</label>
                                                <textarea class="form-control wysiwyg" name="contenu[]" rows="4"><?php echo $section['contenu']; ?></textarea>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Images de section (upload multiple)</label>
                                                    <?php if (!empty($section['images'])): ?>
                                                        <ul class="small text-muted mb-2 ps-3">
                                                            <?php foreach ($section['images'] as $img): ?>
                                                                <li><?php echo htmlspecialchars($img['path'], ENT_QUOTES, 'UTF-8'); ?> (alt : <?php echo htmlspecialchars($img['alt_image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)</li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" name="section_image[<?php echo $idx; ?>][]" accept="image/*" multiple>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Textes alternatifs (séparez par ";")</label>
                                                    <input type="text" class="form-control" name="section_image_alt[]" value="<?php echo htmlspecialchars(implode('; ', array_column($section['images'], 'alt_image')) , ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ordre : images existantes puis nouvelles">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="border rounded p-3 mb-3 section-block" data-index="0">
                                        <input type="hidden" name="detail_id[]" value="">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">Section 1</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-section">Supprimer</button>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Sous-titre</label>
                                            <textarea class="form-control wysiwyg" name="sous_titre[]" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contenu</label>
                                            <textarea class="form-control wysiwyg" name="contenu[]" rows="4"></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Images de section (upload multiple)</label>
                                                <input type="file" class="form-control" name="section_image[0][]" accept="image/*" multiple>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Textes alternatifs (séparez par ";")</label>
                                                <input type="text" class="form-control" name="section_image_alt[]" placeholder="Ordre : images existantes puis nouvelles">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="section_remove[]" value="">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-outline-secondary" href="liste.php">Annuler</a>
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const tinyConfig = {
            selector: '.wysiwyg',
            menubar: false,
            plugins: 'link image lists code',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image | code',
            block_formats: 'Paragraphe=p;Titre 1=h1;Titre 2=h2;Titre 3=h3;Titre 4=h4;Citation=blockquote',
            height: 220,
            branding: false,
            convert_urls: false,
            readonly: false,
            forced_root_block: false,
            encoding: 'utf-8',
            entity_encoding: 'raw',
            force_p_newlines: false,
            force_br_newlines: true,
        };
        if (window.tinymce) {
            tinymce.remove();
            tinymce.init(tinyConfig);
        }

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
                <input type="hidden" name="detail_id[]" value="">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Section ${idx + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section">Supprimer</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sous-titre</label>
                    <textarea class="form-control wysiwyg" name="sous_titre[]" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea class="form-control wysiwyg" name="contenu[]" rows="4"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Images de section (upload multiple)</label>
                        <input type="file" class="form-control" name="section_image[${idx}][]" accept="image/*" multiple>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Textes alternatifs (séparez par ";")</label>
                        <input type="text" class="form-control" name="section_image_alt[]" placeholder="Ordre : images existantes puis nouvelles">
                    </div>
                </div>
            `;
            wrapper.appendChild(block);
            if (window.tinymce) {
                tinymce.remove();
                tinymce.init(tinyConfig);
            }
        });

        wrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-section')) {
                const detailId = e.target.getAttribute('data-detail') || '';
                if (detailId) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'section_remove[]';
                    hidden.value = detailId;
                    wrapper.appendChild(hidden);
                }
                e.target.closest('.section-block').remove();
            }
        });
    </script>
</body>
</html>