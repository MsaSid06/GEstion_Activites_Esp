<?php

// api_register.php
header('Content-Type: application/json');
require_once '../Gestionnaire/config/connexion.php';

require_once '../Gestionnaire/models/etudiant.php';
require_once '../Gestionnaire/models/personnel.php';
require_once '../Gestionnaire/models/utilisateur.php';

$pdo = connexionBD();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["error" => "Données invalides."]);
    exit;
}

$nom        = $input['nom'] ?? '';
$prenom     = $input['prenom'] ?? '';
$email      = $input['email'] ?? '';
$tel        = $input['tel'] ?? '';
$mot_de_passe = $input['mot_de_passe'] ?? '';
$profil     = $input['profil'] ?? '';
$filiere    = $input['filiere'] ?? '';
$niveau     = $input['niveau'] ?? '';
$poste      = $input['poste'] ?? '';
$specialite = $input['specialite'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Email invalide."]);
    exit;
}

try {

    // Génération du matricule unique par préfixe
    $pdo->beginTransaction();
    $sql = "SELECT matricule_user
        FROM utilisateur
        ORDER BY matricule_user DESC
        LIMIT 1";

    $stmt = $pdo->query($sql);
    $dernierMatricule = $stmt->fetchColumn();
    if (!$dernierMatricule) {
        $matricule = "U001";
    } else {
        $numero = (int) substr($dernierMatricule, 1);
        $numero++;

        $matricule = "U" . str_pad($numero, 3, "0", STR_PAD_LEFT);
    }

    $mot_de_passe = password_hash($mot_de_passe,PASSWORD_DEFAULT);

    if ($profil === "ETUDIANT") {

        $user =  creerUtilisateur($pdo, $matricule, $nom, $prenom, $email, $tel, $mot_de_passe, $profil, 1);
        $etd =  creerEtudiant($pdo, $matricule, $filiere, $niveau);
        if ($etd && $user) {
            echo json_encode([
                 "success" => true,
                 "message" => "Inscription réussie ! Votre matricule est : $matricule"
             ]);
        } else {
            echo json_encode([
                 "success" => false,
                 "message" => "Veuillez saisir les bonnes valeures"
             ]);

        }
    } else {

        $user =  creerUtilisateur($pdo, $matricule, $nom, $prenom, $email, $tel, $mot_de_passe, $profil, 1);
        $etd =  creerPersonnel($pdo, $matricule, $poste, $specialite);
        if ($etd && $user) {
            echo json_encode([
                 "success" => true,
                 "message" => "Inscription réussie ! Votre matricule est : $matricule"
             ]);
        } else {
            echo json_encode([
                 "success" => false,
                 "message" => "Veuillez saisir les bonnes valeures"
             ]);

        }

    }$pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["error" => "Erreur lors de l'inscription : " . $e->getMessage()]);
}
