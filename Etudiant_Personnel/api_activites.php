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
            a.id_act,
            a.titre,
            a.description,
            a.type_act,
            a.date_debut,
            a.date_fin,
            a.lieu,
            s.nom_struct,
            s.type_struct,
            CASE 
                WHEN a.date_fin < NOW() THEN 'Terminé'
                WHEN a.date_debut <= NOW() AND a.date_fin >= NOW() THEN 'En cours'
                ELSE 'À venir'
            END AS statut
        FROM ACTIVITE a
        LEFT JOIN GESTIONNAIRE g ON a.matricule_user = g.matricule_user
        LEFT JOIN STRUCTURE s ON g.id_struct = s.id_struct
        ORDER BY a.date_debut ASC
    ");
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($activites);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
