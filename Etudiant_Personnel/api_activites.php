<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['matricule_user'])) {
    echo json_encode(["error" => "Non autorisé"]);
    exit;
}
require_once 'db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            id_activite,
            titre,
            description,
            date_debut,
            date_fin,
            lieu,
            departement,
            CASE 
                WHEN date_fin < CURDATE() THEN 'Terminé'
                WHEN date_debut <= CURDATE() AND date_fin >= CURDATE() THEN 'En cours'
                ELSE 'À venir'
            END AS statut
        FROM ACTIVITE
        ORDER BY date_debut ASC
    ");
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($activites);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
