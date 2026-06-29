<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['matricule_user'])) {
    echo json_encode(["error" => "Non autorisé"]);
    exit;
}
require_once '../Gestionnaire/config/connexion.php';

try {
    $pdo = connexionBD();

    $stmt = $pdo->query("
        SELECT 
            id_act,
            titre,
            description,
            type_act,
            date_debut,
            date_fin,
            lieu,
            CASE 
                WHEN date_fin < NOW() THEN 'Terminé'
                WHEN date_debut <= NOW() AND date_fin >= NOW() THEN 'En cours'
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
