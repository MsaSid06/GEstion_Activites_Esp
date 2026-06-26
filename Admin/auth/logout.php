<?php
/**
 * auth/logout.php
 * Ferme la session puis renvoie vers la page de connexion.
 */

require_once __DIR__ . '/../includes/auth.php';

deconnecter_utilisateur();
set_flash('succes', 'Vous avez été déconnecté.');
// $retour = __DIR__ . "/../../index.php"
// redirect($retour);
header("Location: /GestionDesActiviteEsp/index.php");
