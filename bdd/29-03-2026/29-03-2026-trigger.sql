-- Création d'une table pour stocker les compteurs
CREATE TABLE table_sequences (
    table_name VARCHAR(50) PRIMARY KEY,
    current_value INT NOT NULL DEFAULT 0
);

-- Initialisation pour vos tables
INSERT INTO table_sequences (table_name) VALUES ('article'), ('article_details'), ('image'), ('Categorie'), ('user');

-- Exemple de Trigger pour la table Categorie
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

-- Exemple de Trigger pour la table article
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

-- Exemple de Trigger pour la table article_details
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

-- Exemple de Trigger pour la table image
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

-- Exemple de Trigger pour la table user
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

