<?php
// Configuration de la base de données
define('DB_HOST', 'db');
define('DB_NAME', 'Iran_War');
define('DB_USER', 'root'); 
define('DB_PASS', 'iranwar'); 

/**
 * Fonction pour obtenir une connexion PDO à la base de données
 * @return PDO
 */
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            // Utilisation de utf8mb4 (plus complet que utf8 pour MySQL)
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Commande critique pour forcer la communication en UTF-8 dès la connexion
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}
?>