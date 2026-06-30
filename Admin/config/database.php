<?php
/**
 * config/database.php
 * Connexion centralisée à la base de données (PDO).
 *
 * Adapte ces constantes à ton environnement XAMPP si besoin.
 * Par défaut sous XAMPP : utilisateur "root", aucun mot de passe.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_activites_esp');
define('DB_USER', 'root');
define('DB_PASS', 'lome2006');
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // les erreurs SQL deviennent des exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // résultats sous forme de tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                   // vraies requêtes préparées (sécurité)
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production on n'affiche jamais le détail de l'erreur à l'utilisateur.
    http_response_code(500);
    exit('Connexion à la base de données impossible. Vérifie que MySQL (XAMPP) est démarré.');
}
