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
            id_struct,
            nom_struct,
            desc_struct,
            email,
            tel,
            type_struct
        FROM structure
        ORDER BY nom_struct ASC
    ");

    $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($structures);
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
    
}
?>
