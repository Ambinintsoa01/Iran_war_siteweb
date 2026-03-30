<?php
require_once __DIR__ . '/front_helpers.php';
$title = $pageTitle ?? 'Le Journal';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title); ?></title>
    <link rel="stylesheet" href="<?php echo baseUrl('assets/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        body { background: #f8fafc; color: #0f172a; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .page-shell { max-width: 1100px; margin: 0 auto; padding: 1.25rem 1.5rem 2rem; }
        .site-nav { display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.85rem 1.1rem; box-shadow: 0 10px 24px rgba(15,23,42,0.08); margin-bottom: 1rem; }
        .brand { font-weight: 800; letter-spacing: 0.04em; color: #0f172a; }
        .nav-links { display: flex; gap: 1rem; align-items: center; }
        .nav-links a { color: #475569; text-decoration: none; font-weight: 600; }
        .nav-links a:hover { color: #0f172a; }
        .nav-links .login { color: #2563eb; font-weight: 700; }
        .layout { display: grid; grid-template-columns: 250px 1fr; gap: 1.25rem; align-items: start; }
        .sidebar { width: 100%; background-color: #1A1A1A; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.5); border-radius: 10px; position: sticky; top: 16px; }
        .sidebar h3 { color: #FFFFFF; font-size: 1.1rem; margin-bottom: 12px; border-bottom: 1px solid #333; padding-bottom: 8px; }
        .sidebar nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar nav ul li { margin-bottom: 10px; }
        .sidebar nav ul li a { color: #CCCCCC; text-decoration: none; display: block; padding: 10px 12px; border-radius: 6px; transition: background-color 0.3s, color 0.3s; }
        .sidebar nav ul li a:hover, .sidebar nav ul li a.active { background-color: #333; color: #FFFFFF; }
        .article-card { position: relative; border-radius: 14px; overflow: hidden; background: #ffffff; border: 1px solid #e5e7eb; display: grid; grid-template-columns: 1.3fr 1fr; min-height: 300px; box-shadow: 0 20px 60px rgba(15,23,42,0.08); }
        .article-card img { width: 100%; height: 100%; object-fit: cover; }
        .article-card .copy { padding: 1.75rem; font-family: "Merriweather", "Times New Roman", serif; }
        .kicker { display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.32rem 0.85rem; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 999px; color: #0c4a6e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; }
        .article-title { font-size: 2rem; margin: 1rem 0 0.5rem; line-height: 1.2; color: #0f172a; }
        .lead { font-size: 1.05rem; color: #334155; max-width: 54ch; }
        .empty { padding: 2rem; text-align: center; border: 1px dashed #cbd5e1; border-radius: 12px; background: #ffffff; color: #475569; }
        .hero { position: relative; border-radius: 16px; overflow: hidden; background: #0f172a; color: #e2e8f0; min-height: 320px; box-shadow: 0 20px 60px rgba(15,23,42,0.1); margin-bottom: 1.5rem; }
        .hero img { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.75); }
        .hero .overlay { position: absolute; inset: 0; background: linear-gradient(120deg, rgba(15,23,42,0.8), rgba(15,23,42,0.25)); }
        .hero .content { position: absolute; bottom: 0; left: 0; right: 0; padding: 2rem; }
        .hero .article-title { color: #e2e8f0; font-size: 2.4rem; }
        .hero .lead { color: #cbd5e1; max-width: 60ch; }
        .section { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 12px 28px rgba(15,23,42,0.06); }
        .section h2 { font-family: "Merriweather", "Times New Roman", serif; font-size: 1.4rem; color: #0f172a; margin-bottom: 0.65rem; }
        .section p { color: #1f2937; line-height: 1.6; font-size: 1.02rem; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-top: 0.75rem; }
        .gallery img { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb; }
        .not-found { padding: 2rem; text-align: center; border: 1px dashed #cbd5e1; border-radius: 12px; background: #ffffff; color: #475569; margin-top: 1rem; }
        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .article-card { grid-template-columns: 1fr; }
            .article-card img { height: 240px; }
            .hero { min-height: 260px; }
            .hero .article-title { font-size: 2rem; }
            .sidebar { position: static; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="site-nav">
            <div class="brand">Le Journal</div>
            <div class="nav-links">
                <a href="<?php echo frontUrl(''); ?>">Accueil</a>
                <a href="#">Le journal</a>
                <a href="#">Services</a>
                <a class="login" href="<?php echo baseUrl('login.php'); ?>">Admin</a>
            </div>
        </div>
