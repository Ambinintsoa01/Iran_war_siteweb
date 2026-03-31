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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* 1. Import des polices (Playfair pour le côté Journal) */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700&display=swap');

        :root {
            --paper-dark: #1a1a1a;
            --paper-blue: #2b41e2;
            --border-light: #ececec;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #fff; 
            color: var(--paper-dark);
        }

        /* 2. Barre de Navigation Moderne (Style PaperMag) */
        .site-header {
            border-bottom: 1px solid var(--border-light);
        }

        .top-info {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #666;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .brand-section {
            padding: 30px 0;
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 900;
            color: var(--paper-dark);
            text-decoration: none;
            letter-spacing: -1px;
        }

        .nav-bar-container {
            border-top: 1px solid var(--border-light);
            border-bottom: 2px solid var(--paper-dark);
        }

        .nav-modern {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-modern .nav-link {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--paper-dark);
            padding: 15px 20px;
            transition: color 0.2s;
        }

        .nav-modern .nav-link:hover {
            color: var(--paper-blue);
        }

        .nav-modern .login-link {
            color: var(--paper-blue);
        }

        div.col:hover  {
            transform: scale(1.02);
            background-color: rgba(134, 132, 132, 0.2);
            transition: all 0.5s ease;
            
        }

        /* 3. Utilitaires pour le contenu */
        h1, h2, h3 { font-family: 'Playfair Display', serif; font-weight: 900; }
        a { text-decoration: none; }
    </style>
</head>
<body>

    <header class="site-header">
        <div class="top-info">
            <div class="container-fluid px-md-5 d-flex justify-content-between">
                <div><?php echo date('l, d F Y'); ?> — Antananarivo, Madagascar</div>
                <div class="d-none d-md-block">Trending : Iran War, Impact, Situation</div>
            </div>
        </div>

        <div class="brand-section text-center">
            <div class="container">
                <a href="<?php echo frontUrl(''); ?>" class="brand-logo">The News</a>
            </div>
        </div>

        <div class="nav-bar-container">
            <div class="container">
                <ul class="nav-modern">
                    <li><a href="<?php echo frontUrl(''); ?>" class="nav-link">Accueil</a></li>
                    <li><a href="<?php echo baseUrl('login.php'); ?>" class="nav-link login-link">Admin</a></li>
                </ul>
            </div>
        </div>
    </header>

