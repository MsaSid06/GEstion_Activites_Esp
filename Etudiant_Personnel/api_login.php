<?php

// api_login.php
header('Content-Type: application/json');
require_once '../Gestionnaire/config/connexion.php';
$pdo = connexionBD();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["error" => "Données invalides."]);
    exit;
}

$email = $input['email'] ?? '';
$mot_de_passe = $input['mot_de_passe'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM UTILISATEUR WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
        echo json_encode(["error" => "Email ou mot de passe incorrect."]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "matricule" => $user['matricule_user'],
        "nom" => $user['nom'],
        "prenom" => $user['prenom'],
        "profil" => $user['profil']
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => "Erreur lors de la connexion : " . $e->getMessage()]);
}
