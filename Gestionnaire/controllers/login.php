<?php

session_start();
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../models/utilisateur.php';


$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email && $password) {
    $pdo = connexionBD();
    $user = getUtilisateurParMail($pdo, $email);


    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['matricule_user'] = $user['matricule_user'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['profil'] = $user['profil'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        switch ($_SESSION['profil']) {
            case 'ADMIN':
                echo "3";
                break;

            case 'GESTIONNAIRE':
                echo "2";
                break;

            case 'ETUDIANT' || 'PERSONNEL':
                echo "1";
                break;

        }
    } else {
        echo "<h4 style='color:red'>invalid </h4>";
    }

} else {
    echo "<h4 style='color:red'>veuillez remplir les champs</h4>";
}
