<?php
// Récupération des menus pour la sidebar
$stmt = $pdo->query("SELECT * FROM menu ORDER BY menu_mere, level");
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Point d'ancrage pour générer des liens cohérents depuis les sous-dossiers (ex: ../)
if (!isset($sidebarBaseUrl)) {
    $sidebarBaseUrl = '';
}

// Fonction pour organiser les menus en hiérarchie basée sur menu_mere
function buildMenu($menus) {
    $menuMap = [];

    // Préparer les entrées et référencer pour que les enfants se propagent
    foreach ($menus as &$menu) {
        $menu['children'] = [];
        $menuMap[$menu['menu_id']] = &$menu;
    }
    unset($menu); // éviter de garder la référence

    // Attacher les enfants à leur parent
    foreach ($menuMap as $menuId => &$menu) {
        $parentId = $menu['menu_mere'];
        if (!empty($parentId) && $parentId !== $menuId && isset($menuMap[$parentId])) {
            $menuMap[$parentId]['children'][] = &$menuMap[$menuId];
        }
    }
    unset($menu);

    // Extraire uniquement les racines (niveau 1 ou menu_mere == menu_id)
    $menuTree = [];
    foreach ($menuMap as $menuId => $menu) {
        if (empty($menu['menu_mere']) || $menu['menu_mere'] === $menuId || (int)$menu['level'] === 1) {
            $menuTree[$menuId] = $menu;
        }
    }

    return $menuTree;
}

$menuTree = buildMenu($menus);
?>

<aside class="sidebar">
    <h2>Backoffice</h2>
    <nav>
        <ul>
            <?php foreach ($menuTree as $menu): ?>
                <li>
                    <?php if (!empty($menu['children'])): ?>
                        <a href="#" onclick="toggleSubmenu('submenu-<?php echo $menu['slug_menu']; ?>'); return false;">
                            <span class="toggle-icon">+</span> <?php echo $menu['nom']; ?>
                        </a>
                        <ul id="submenu-<?php echo $menu['slug_menu']; ?>" class="submenu" style="display: none;">
                            <?php foreach ($menu['children'] as $child): ?>
                                <li><a href="<?php echo $sidebarBaseUrl . $menu['slug_menu']; ?>/<?php echo $child['slug_menu']; ?>.php"><?php echo $child['nom']; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <a href="<?php echo $sidebarBaseUrl . $menu['slug_menu']; ?>.php"><?php echo $menu['nom']; ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <a href="<?php echo $sidebarBaseUrl; ?>logout.php">Déconnexion</a>
</aside>

<script>
function toggleSubmenu(id) {
    var submenu = document.getElementById(id);
    var icon = submenu.previousElementSibling.querySelector('.toggle-icon');
    if (submenu.style.display === 'none') {
        submenu.style.display = 'block';
        icon.textContent = '-';
    } else {
        submenu.style.display = 'none';
        icon.textContent = '+';
    }
}
</script>