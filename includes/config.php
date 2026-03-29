<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'Iran_War');
define('DB_USER', 'root'); // À adapter selon votre configuration
define('DB_PASS', ''); // À adapter selon votre configuration

/**
 * Fonction pour obtenir une connexion PDO à la base de données
 * @return PDO
 */
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}
?>