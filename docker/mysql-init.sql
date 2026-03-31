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
    level INT NOT NULL DEFAULT 1,
    menu_mere VARCHAR(255)
);

-- Hiérarchie des menus (menu_mere référence le menu parent)
INSERT INTO menu (menu_id, nom, description, slug_menu, level, menu_mere) VALUES 
('MENU-1', 'Dashboard', 'Tableau de bord principal', 'dashboard', 1, NULL),

('MENU-2', 'Articles', 'Gestion des articles', 'articles', 1, NULL),
('MENU-3', 'Liste articles', 'Liste des articles', 'liste', 2, 'MENU-2'),
('MENU-4', 'Saisie article', 'Saisie d''un nouvel article', 'saisie', 2, 'MENU-2'),

('MENU-5', 'Catégories', 'Gestion des catégories', 'categories', 1, NULL),
('MENU-6', 'Liste catégories', 'Liste des catégories', 'liste', 2, 'MENU-5'),
('MENU-7', 'Create catégories', 'Création d''une nouvelle catégorie', 'saisie', 2, 'MENU-5'),

('MENU-8', 'Utilisateurs', 'Gestion des utilisateurs', 'utilisateurs', 1, NULL),
('MENU-9', 'Liste utilisateurs', 'Liste des utilisateurs', 'liste', 2, 'MENU-8'),
('MENU-10', 'Saisie utilisateur', 'Saisie d''un nouvel utilisateur', 'saisie', 2, 'MENU-8');

-- 4) 29-03-2026-data.sql
-- (corrigé pour correspondre au schéma user)
INSERT INTO user (user_id, email, password) VALUES ('USR-1', 'admin@gmail.com', 'admin!@#123');

-- 5) 30-03-2026-data.sql
INSERT INTO menu (menu_id, nom, description, slug_menu, level, menu_mere) VALUES
('MENU-11', 'Preview', 'Preview', 'preview', 1, NULL);

-- 6) 30-03-2026-update.sql
ALTER TABLE article ADD COLUMN alt_img VARCHAR(255);

-- Données de test pour la guerre en Iran

-- Catégories
INSERT INTO Categorie (categorie_id, nom, slug_cat) VALUES
('CAT-1', 'Contexte historique', 'contexte-historique'),
('CAT-2', 'Chronologie du conflit', 'chronologie-conflit'),
('CAT-3', 'Fronts et batailles', 'fronts-et-batailles'),
('CAT-4', 'Vie quotidienne en temps de guerre', 'vie-quotidienne-guerre'),
('CAT-5', 'Analyses géopolitiques', 'analyses-geopolitiques'),
('CAT-6', 'Mémoire et reconstruction', 'memoire-et-reconstruction');

-- Articles principaux
INSERT INTO article (article_id, titre, slug, resume, image_principale, alt_img, id_categorie, date_publication) VALUES
('ART-1', 'Aux origines du conflit Iran-Irak', 'origines-conflit-iran-irak',
 'Un aperçu des tensions politiques, religieuses et territoriales qui ont préparé le terrain à la guerre.',
 'https://images.pexels.com/photos/1405614/pexels-photo-1405614.jpeg',
 'Vue aérienne d''une région désertique au Moyen-Orient', 'CAT-1', NOW() - INTERVAL 90 DAY),

('ART-2', 'La révolution iranienne et ses conséquences régionales', 'revolution-iranienne-consequences',
 'Comment la révolution de 1979 a redéfini les équilibres internes et externes de l''Iran.',
 'https://images.pexels.com/photos/1047540/pexels-photo-1047540.jpeg',
 'Manifestation de foule vue de loin', 'CAT-1', NOW() - INTERVAL 85 DAY),

('ART-3', 'Premières offensives et réactions internationales', 'premieres-offensives-reactions-internationales',
 'Les premiers mois du conflit et la manière dont la communauté internationale y a réagi.',
 'https://images.pexels.com/photos/1119080/pexels-photo-1119080.jpeg',
 'Ciel sombre avec fumée à l''horizon', 'CAT-2', NOW() - INTERVAL 70 DAY),

('ART-4', 'La guerre de tranchées sur le front sud', 'guerre-tranchees-front-sud',
 'Conditions de vie des soldats et stratégies militaires sur les principaux fronts.',
 'https://images.pexels.com/photos/799443/pexels-photo-799443.jpeg',
 'Paysage boueux avec lignes de terrain marquées', 'CAT-3', NOW() - INTERVAL 60 DAY),

('ART-5', 'Vivre à Téhéran sous les bombardements', 'vivre-teheran-bombardements',
 'Témoignages de familles restées en ville malgré les alertes et les coups de sirène.',
 'https://images.pexels.com/photos/1118869/pexels-photo-1118869.jpeg',
 'Silhouette d''une ville au crépuscule', 'CAT-4', NOW() - INTERVAL 55 DAY),

('ART-6', 'Équilibres régionaux et jeux d''alliances', 'equilibres-regionaux-alliances',
 'Le rôle des grandes puissances et des pays voisins dans le prolongement du conflit.',
 'https://images.pexels.com/photos/3183126/pexels-photo-3183126.jpeg',
 'Carte politique stylisée sur une table lumineuse', 'CAT-5', NOW() - INTERVAL 45 DAY),

('ART-7', 'L''impact humain : pertes, exils et traumatismes', 'impact-humain-pertes-exils-traumatismes',
 'Un regard sobre sur le coût humain du conflit pour les civils et les combattants.',
 'https://images.pexels.com/photos/1415131/pexels-photo-1415131.jpeg',
 'Personnes marchant sur une route poussiéreuse', 'CAT-4', NOW() - INTERVAL 40 DAY),

('ART-8', 'L''après-guerre : reconstruction et mémoire', 'apres-guerre-reconstruction-memoire',
 'Comment les villes, les familles et les institutions se reconstruisent après les combats.',
 'https://images.pexels.com/photos/373912/pexels-photo-373912.jpeg',
 'Grue de chantier sur fond de ciel clair', 'CAT-6', NOW() - INTERVAL 30 DAY),

('ART-9', 'La place du conflit dans la mémoire iranienne', 'memoire-conflit-societe-iranienne',
 'Cérémonies, musées et récits qui entretiennent le souvenir du conflit dans la société.',
 'https://images.pexels.com/photos/256381/pexels-photo-256381.jpeg',
 'Bougies allumées lors d''une veillée', 'CAT-6', NOW() - INTERVAL 20 DAY),

('ART-10', 'Regards croisés : historiens et témoins', 'regards-croises-historiens-temoins',
 'Analyse des différentes manières de raconter la guerre selon les acteurs et chercheurs.',
 'https://images.pexels.com/photos/2078266/pexels-photo-2078266.jpeg',
 'Personne feuilletant des archives sur une table', 'CAT-5', NOW() - INTERVAL 10 DAY);

-- Sections détaillées pour quelques articles
INSERT INTO article_details (details_id, article_id, sous_titre, contenu, slug_details) VALUES
('DET-1', 'ART-1', 'Frontières contestées et héritages impériaux',
 'Les frontières héritées de l''époque impériale nourrissent des désaccords anciens.\nCe texte présente sans images ni détails choquants les grandes lignes de ces tensions.',
 'frontieres-contestees'),
('DET-2', 'ART-1', 'Rôle des ressources énergétiques',
 'Le contrôle des routes et des ressources énergétiques joue un rôle central dans les calculs stratégiques des deux États.',
 'role-ressources-energetiques'),

('DET-3', 'ART-4', 'Conditions de vie sur la ligne de front',
 'Les unités stationnées sur le front sud doivent composer avec la boue, le froid et la fatigue.\nLes soldats développent des formes de solidarité pour tenir dans la durée.',
 'conditions-vie-front'),
('DET-4', 'ART-4', 'Adaptation des tactiques',
 'Au fil des mois, les tactiques se transforment : mouvements plus limités, importance accrue du renseignement et de la logistique.',
 'adaptation-tactiques'),

('DET-5', 'ART-5', 'Organisation de la vie quotidienne',
 'Les habitants s''organisent autour des alertes : préparation de sacs d''urgence, repérage des abris les plus proches et entraide de voisinage.',
 'organisation-vie-quotidienne'),
('DET-6', 'ART-5', 'Écoles, marchés et lieux de sociabilité',
 'Malgré l''insécurité, certains services restent ouverts.\nLes écoles adaptent leurs horaires et les marchés deviennent des lieux d''échange d''informations.',
 'ecoles-marches-sociabilite'),

('DET-7', 'ART-8', 'Réhabilitation des infrastructures',
 'Routes, ponts et bâtiments administratifs font l''objet de programmes de réparation étalés sur plusieurs années.',
 'rehabilitation-infrastructures'),
('DET-8', 'ART-8', 'Accompagnement des populations',
 'Des dispositifs d''aide psychologique et sociale se mettent progressivement en place pour accompagner les personnes les plus touchées.',
 'accompagnement-populations'),

-- Ajout de sous-sections supplémentaires pour que chaque article ait au moins 2 sous-titres
('DET-9',  'ART-2', 'Évolution du paysage politique intérieur',
 'Les institutions se recomposent progressivement après la révolution, avec de nouveaux équilibres entre acteurs politiques et religieux.',
 'evolution-paysage-politique'),
('DET-10', 'ART-2', 'Nouvelles attentes de la population',
 'Une partie de la population exprime des attentes fortes en matière de justice sociale, de services publics et de participation aux décisions.',
 'attentes-population'),

('DET-11', 'ART-3', 'Réactions des organisations internationales',
 'Les organisations régionales et internationales publient communiqués et résolutions, tout en cherchant des canaux de médiation.',
 'reactions-organisations-internationales'),
('DET-12', 'ART-3', 'Premiers effets sur les échanges économiques',
 'Les flux commerciaux se réorientent, certains secteurs connaissant des ralentissements marqués tandis que d''autres s''adaptent.',
 'effets-echanges-economiques'),

('DET-13', 'ART-6', 'Positions des puissances voisines',
 'Les pays voisins ajustent leurs alliances et leurs discours publics, en tenant compte de leurs propres priorités de sécurité.',
 'positions-puissances-voisines'),
('DET-14', 'ART-6', 'Rôle des grandes puissances',
 'Les grandes puissances utilisent une combinaison d''initiatives diplomatiques, de livraisons de matériel et de prises de position publiques.',
 'role-grandes-puissances'),

('DET-15', 'ART-7', 'Déplacements de population',
 'Certaines familles se déplacent vers des régions jugées plus sûres, modifiant la répartition démographique de plusieurs provinces.',
 'deplacements-population'),
('DET-16', 'ART-7', 'Prise en charge des personnes fragilisées',
 'Des réseaux de soutien se structurent pour accompagner les personnes isolées, les enfants et les personnes âgées.',
 'prise-en-charge-personnes-fragilisees'),

('DET-17', 'ART-9', 'Lieux de mémoire et commémorations',
 'Des monuments, musées et journées commémoratives contribuent à entretenir un souvenir collectif du conflit.',
 'lieux-memoire-commemorations'),
('DET-18', 'ART-9', 'Récits familiaux et transmission',
 'Les récits échangés au sein des familles participent à la manière dont les jeunes générations perçoivent cette période.',
 'recits-familiaux-transmission'),

('DET-19', 'ART-10', 'Approches des historiens',
 'Les chercheurs mobilisent différentes sources et méthodologies pour analyser les événements sans parti pris.',
 'approches-historiens'),
('DET-20', 'ART-10', 'Témoignages individuels et pluralité des points de vue',
 'Les témoignages recueillis mettent en lumière la diversité des expériences vécues, sans entrer dans des descriptions graphiques.',
 'temoignages-individuels-points-de-vue');

-- Images associées à certaines sections
INSERT INTO image (image_id, details_id, path, alt_image) VALUES
('IMG-1', 'DET-1', 'https://images.pexels.com/photos/314937/pexels-photo-314937.jpeg', 'Carte ancienne illustrant des frontières régionales'),
('IMG-2', 'DET-3', 'https://images.pexels.com/photos/3993433/pexels-photo-3993433.jpeg', 'Paysage de terrain avec ciel chargé'),
('IMG-3', 'DET-5', 'https://images.pexels.com/photos/3804721/pexels-photo-3804721.jpeg', 'Rue animée dans une grande ville moyen-orientale'),
('IMG-4', 'DET-7', 'https://images.pexels.com/photos/210617/pexels-photo-210617.jpeg', 'Chantier de reconstruction urbain');

