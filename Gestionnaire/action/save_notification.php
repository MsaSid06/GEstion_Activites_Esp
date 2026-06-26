<?php

session_start();
require_once "../config/connexion.php";
require_once "../models/notifications.php";

$pdo = connexionBD();

$id_emetteur = $_SESSION['matricule_user'];
$id_act = !empty($_POST['id_act']) ? (int)$_POST['id_act'] : null;
$message = trim($_POST['message']);

if ($message === '') {
    die("Message obligatoire");
}

if ($id_act < 0) {
    die("Aucune Activite avec une ID negatif");
}

creerNotification($pdo, $id_emetteur, $id_act, $message);

header("Location: ../action/Affiche_notif.php");
exit;
