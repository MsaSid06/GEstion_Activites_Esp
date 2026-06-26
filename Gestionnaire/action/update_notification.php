<?php

session_start();
require_once "../config/connexion.php";
require_once "../models/notifications.php";

$pdo = connexionBD();

if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}

$id_not = (int) $_POST['id_not'];
$id_act = !empty($_POST['id_act']) ? (int) $_POST['id_act'] : null;
$message = trim($_POST['message']);

if ($message === '') {
    header("Location: ./Affiche_notif.php");
    exit;
}

$sql = "UPDATE NOTIFICATION
        SET id_act = :id_act,
            message = :message
        WHERE id_not = :id_not";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(':id_act', $id_act, $id_act === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':message', $message, PDO::PARAM_STR);
$stmt->bindValue(':id_not', $id_not, PDO::PARAM_INT);

$stmt->execute();

header("Location: ./Affiche_notif.php");
exit;
