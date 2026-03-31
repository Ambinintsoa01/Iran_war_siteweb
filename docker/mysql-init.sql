-- Init MySQL from raw bdd scripts

-- 1) 29-03-2026-script.sql
CREATE DATABASE IF NOT EXISTS Iran_War;
USE Iran_War;

CREATE TABLE Categorie (
  categorie_id varchar(255) PRIMARY KEY,
  nom varchar(255),
  slug_cat varchar(255)
);

CREATE TABLE article (
  article_id varchar(255) PRIMARY KEY,
  titre varchar(255),
  slug varchar(255),
  resume varchar(255),
  image_principale varchar(255),
  date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  id_categorie varchar(255),
  CONSTRAINT fk_article_categorie FOREIGN KEY (id_categorie) REFERENCES Categorie (categorie_id)
);

CREATE TABLE article_details (
  details_id varchar(255) PRIMARY KEY,
  article_id varchar(255),
  sous_titre varchar(255),
  contenu TEXT, 
  slug_details varchar(255),
  CONSTRAINT fk_details_article FOREIGN KEY (article_id) REFERENCES article (article_id)
);

CREATE TABLE image (
  image_id varchar(255) PRIMARY KEY,
  details_id varchar(255),
  path varchar(255),
  alt_image varchar(255),
  CONSTRAINT fk_image_details FOREIGN KEY (details_id) REFERENCES article_details (details_id)
);

CREATE TABLE user (
  user_id varchar(255) PRIMARY KEY,
  email varchar(255) UNIQUE,
  password varchar(255)
);

-- 2) 29-03-2026-trigger.sql
-- Création d'une table pour stocker les compteurs
CREATE TABLE table_sequences (
    table_name VARCHAR(50) PRIMARY KEY,
    current_value INT NOT NULL DEFAULT 0
);

-- Initialisation pour vos tables
INSERT INTO table_sequences (table_name) VALUES ('article'), ('article_details'), ('image'), ('Categorie'), ('user');

-- Trigger pour la table Categorie
DELIMITER //
CREATE TRIGGER before_insert_categorie
BEFORE INSERT ON Categorie
FOR EACH ROW
BEGIN
    UPDATE table_sequences 
    SET current_value = current_value + 1 
    WHERE table_name = 'Categorie';

    IF NEW.categorie_id IS NULL OR NEW.categorie_id = '' THEN
        SET NEW.categorie_id = (
            SELECT CONCAT('CAT-', current_value) 
            FROM table_sequences 
            WHERE table_name = 'Categorie'
        );
    END IF;
END //
DELIMITER ;

-- Trigger pour la table article
DELIMITER //
CREATE TRIGGER before_insert_article
BEFORE INSERT ON article
FOR EACH ROW
BEGIN
    UPDATE table_sequences 
    SET current_value = current_value + 1 
    WHERE table_name = 'article';

    IF NEW.article_id IS NULL OR NEW.article_id = '' THEN
        SET NEW.article_id = (
            SELECT CONCAT('ART-', current_value) 
            FROM table_sequences 
            WHERE table_name = 'article'
        );
    END IF;
END //
DELIMITER ;

-- Trigger pour la table article_details
DELIMITER //
CREATE TRIGGER before_insert_article_details
BEFORE INSERT ON article_details
FOR EACH ROW
BEGIN
    UPDATE table_sequences 
    SET current_value = current_value + 1 
    WHERE table_name = 'article_details';

    IF NEW.details_id IS NULL OR NEW.details_id = '' THEN
        SET NEW.details_id = (
            SELECT CONCAT('DET-', current_value) 
            FROM table_sequences 
            WHERE table_name = 'article_details'
        );
    END IF;
END //
DELIMITER ;

-- Trigger pour la table image
DELIMITER //
CREATE TRIGGER before_insert_image
BEFORE INSERT ON image
FOR EACH ROW
BEGIN
    UPDATE table_sequences 
    SET current_value = current_value + 1 
    WHERE table_name = 'image';

    IF NEW.image_id IS NULL OR NEW.image_id = '' THEN
        SET NEW.image_id = (
            SELECT CONCAT('IMG-', current_value) 
            FROM table_sequences 
            WHERE table_name = 'image'
        );
    END IF;
END //
DELIMITER ;

-- Trigger pour la table user
DELIMITER //
CREATE TRIGGER before_insert_user
BEFORE INSERT ON user
FOR EACH ROW
BEGIN
    UPDATE table_sequences 
    SET current_value = current_value + 1 
    WHERE table_name = 'user';

    IF NEW.user_id IS NULL OR NEW.user_id = '' THEN
        SET NEW.user_id = (
            SELECT CONCAT('USR-', current_value) 
            FROM table_sequences 
            WHERE table_name = 'user'
        );
    END IF;
END //      
DELIMITER ;

-- Création d'une table pour stocker les menus (BACKOFFICE)
CREATE TABLE IF NOT EXISTS menu (
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
('MENU-4', 'Saisie article', 'Saisie d''un nouvel article', 'saisie', 2),

('MENU-5', 'Catégories', 'Gestion des catégories', 'categories', 1),
('MENU-6', 'Liste catégories', 'Liste des catégories', 'liste', 2),
('MENU-7', 'Create catégories', 'Création d''une nouvelle catégorie', 'saisie', 2),

('MENU-8', 'Utilisateurs', 'Gestion des utilisateurs', 'utilisateurs', 1),
('MENU-9', 'Liste utilisateurs', 'Liste des utilisateurs', 'liste', 2),
('MENU-10', 'Saisie utilisateur', 'Saisie d''un nouvel utilisateur', 'saisie', 2);

-- 4) 29-03-2026-data.sql
-- (corrigé pour correspondre au schéma user)
INSERT INTO user (user_id, email, password) VALUES ('USR-1', 'admin@gmail.com', 'admin!@#123');

-- 5) 30-03-2026-data.sql
INSERT INTO menu (menu_id, nom, description, slug_menu, level) VALUES
('MENU-11', 'Preview', 'Preview', 'preview', 1);

-- 6) 30-03-2026-update.sql
ALTER TABLE menu ADD COLUMN menu_mere VARCHAR(255);

ALTER TABLE article ADD COLUMN alt_img VARCHAR(255);

