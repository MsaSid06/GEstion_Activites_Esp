<?php

session_start();

define("ROOT", "/GestionDesActiviteEsp");


require_once ROOT . '/Gestionnaire/config/connexion.php';
require_once ROOT .  '/Gestionnaire/models/notifications.php';

$pdo = connexionBD();

if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}

// Récupération ID
$id_not = isset($_POST['id_not']) ? (int) $_POST['id_not'] : 0;

if ($id_not <= 0) {
    header("Location: ./Affiche_notif.php");
    exit;
}

try {

    supprimerNotification($pdo, $id_not);

} catch (PDOException $e) {
    error_log("Erreur suppression notification: " . $e->getMessage());
}

// retour liste
header("Location: ./Affiche_notif.php");
exit;
