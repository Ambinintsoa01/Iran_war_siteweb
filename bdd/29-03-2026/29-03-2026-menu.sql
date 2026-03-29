-- Création d'une table pour stocker les menus (BACKOFFICE)
CREATE OR REPLACE TABLE menu (
    menu_id VARCHAR(50) PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    slug_menu VARCHAR(255) NOT NULL,
    level INT NOT NULL DEFAULT 1
);

INSERT INTO menu (menu_id, nom, description, slug_menu, level) VALUES 
('MENU-1', 'Dashboard', 'Tableau de bord principal', 'dashboard', 1),

('MENU-2', 'Articles', 'Gestion des articles', 'articles', 1),
('MENU-3', 'Liste articles', 'Liste des articles', 'liste', 2),
('MENU-4', 'Saisie article', 'Saisie d\'un nouvel article', 'saisie', 2),

('MENU-5', 'Catégories', 'Gestion des catégories', 'categories', 1),
('MENU-6', 'Liste catégories', 'Liste des catégories', 'liste', 2),
('MENU-7', 'Create catégories', 'Création d\'une nouvelle catégorie', 'saisie', 2),

('MENU-8', 'Utilisateurs', 'Gestion des utilisateurs', 'utilisateurs', 1),
('MENU-9', 'Liste utilisateurs', 'Liste des utilisateurs', 'liste', 2),
('MENU-10', 'Saisie utilisateur', 'Saisie d\'un nouvel utilisateur', 'saisie', 2);


