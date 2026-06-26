<?php
/**
 * auth/logout.php
 * Ferme la session puis renvoie vers la page de connexion.
 */

require_once __DIR__ . '/../includes/auth.php';

deconnecter_utilisateur();
set_flash('succes', 'Vous avez été déconnecté.');
redirect('login.php');
