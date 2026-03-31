<?php declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Le front est servi sous le chemin virtuel "/iran-war-news" (URL publique)
define('BASE_PATH', 'iran-war-news');

function baseUrl(string $path = ''): string {
    $base = trim(BASE_PATH, '/');
    $suffix = ltrim($path, '/');

    // Chemin racine
    if ($suffix === '') {
        return $base === '' ? '/' : '/' . $base . '/';
    }

    // URL absolue à partir de la racine
    return $base === ''
        ? '/' . $suffix
        : '/' . $base . '/' . $suffix;
}

function frontUrl(string $path = ''): string {
    return baseUrl($path);
}

function h($value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function renderHtml(?string $value): string {
    return $value ?? '';
}

function formatDate($dateString): string {
    if (!$dateString) {
        return '';
    }
    try {
        $dt = new DateTime($dateString);
        return $dt->format('d M Y');
    } catch (Exception $e) {
        return (string) $dateString;
    }
}

function imageUrl(?string $path): ?string {
    if (!$path) {
        return null;
    }
    $clean = trim($path);
    $clean = preg_replace('#^\./#', '', $clean);
    $clean = ltrim(preg_replace('#^\.\./#', '', $clean) ?? '', '/\\');
    $clean = str_replace('\\', '/', $clean);
    if (preg_match('#^https?://#i', $clean)) {
        // Proxy les images distantes (ex: Pexels) via notre domaine pour éviter les cookies tiers
        $encoded = rawurlencode($clean);
        return '/image-proxy.php?src=' . $encoded;
    }
    return baseUrl($clean);
}

function fetchCategories(PDO $pdo): array {
    $stmt = $pdo->query('SELECT nom, slug_cat FROM Categorie ORDER BY nom ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchArticles(PDO $pdo, ?string $categorySlug = null): array {
    $where = '';
    $params = [];
    if ($categorySlug !== null && $categorySlug !== '') {
        $where = 'WHERE c.slug_cat = ?';
        $params[] = $categorySlug;
    }
    $sql = 'SELECT a.*, c.nom AS categorie_nom, c.slug_cat FROM article a LEFT JOIN Categorie c ON c.categorie_id = a.id_categorie ' . $where . ' ORDER BY a.date_publication DESC, a.article_id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchArticleBySlug(PDO $pdo, string $slug): ?array {
    $stmt = $pdo->prepare('SELECT a.*, c.nom AS categorie_nom, c.slug_cat FROM article a LEFT JOIN Categorie c ON c.categorie_id = a.id_categorie WHERE a.slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
}

function fetchSections(PDO $pdo, string $articleId): array {
    $stmt = $pdo->prepare('SELECT d.details_id, d.sous_titre, d.contenu, d.slug_details, i.image_id, i.path, i.alt_image FROM article_details d LEFT JOIN image i ON i.details_id = d.details_id WHERE d.article_id = ? ORDER BY d.details_id ASC, i.image_id ASC');
    $stmt->execute([$articleId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sections = [];
    foreach ($rows as $row) {
        $id = $row['details_id'];
        if (!isset($sections[$id])) {
            $sections[$id] = [
                'sous_titre' => $row['sous_titre'],
                'contenu' => $row['contenu'],
                'slug_details' => $row['slug_details'],
                'images' => [],
            ];
        }
        if (!empty($row['path'])) {
            $sections[$id]['images'][] = [
                'path' => $row['path'],
                'alt_image' => $row['alt_image'] ?? '',
            ];
        }
    }
    return array_values($sections);
}
?>
