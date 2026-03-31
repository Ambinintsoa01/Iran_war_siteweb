<?php declare(strict_types=1);

// Proxy simple pour servir les images distantes (Pexels) sans exposer le domaine tiers au navigateur.
// Cela permet d'éviter les cookies tiers signalés par Lighthouse.

$src = $_GET['src'] ?? '';

if ($src === '') {
    http_response_code(400);
    exit('Missing src parameter');
}

$src = filter_var($src, FILTER_SANITIZE_URL) ?: '';

// Décoder car nous utilisons rawurlencode côté PHP
$src = rawurldecode($src);

// Valider le domaine pour éviter d'ouvrir un proxy général
$parts = parse_url($src);
if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
    http_response_code(400);
    exit('Invalid URL');
}

$host = strtolower($parts['host']);
if ($parts['scheme'] !== 'https' || (strpos($host, 'pexels.com') === false && strpos($host, 'images.pexels.com') === false)) {
    http_response_code(403);
    exit('Forbidden host');
}

// Pour les URLs Pexels, demander une version compressée/redimensionnée pour réduire drastiquement le poids.
// Exemple : ?auto=compress&cs=tinysrgb&w=1200
$query = [];
if (!empty($parts['query'])) {
    parse_str($parts['query'], $query);
}
$query['auto'] = 'compress';
$query['cs'] = 'tinysrgb';
$query['w'] = '900';

$rebuilt = $parts['scheme'] . '://' . $parts['host'];
if (!empty($parts['path'])) {
    $rebuilt .= $parts['path'];
}
if (!empty($query)) {
    $rebuilt .= '?' . http_build_query($query);
}

$src = $rebuilt;

// Déterminer un type MIME simple à partir de l'extension
$path = $parts['path'] ?? '';
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = 'image/jpeg';
if ($ext === 'png') {
    $mime = 'image/png';
} elseif ($ext === 'gif') {
    $mime = 'image/gif';
} elseif ($ext === 'webp') {
    $mime = 'image/webp';
}

// Récupérer l'image distante (compressée)
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'follow_location' => 1,
    ],
    'https' => [
        'timeout' => 5,
        'follow_location' => 1,
    ],
]);

$data = @file_get_contents($src, false, $context);

if ($data === false) {
    http_response_code(502);
    exit('Unable to fetch image');
}

header('Content-Type: ' . $mime);
// Mettre en cache longtemps côté navigateur pour de meilleures performances sur les visites suivantes
header('Cache-Control: public, max-age=31536000, immutable');
echo $data;
