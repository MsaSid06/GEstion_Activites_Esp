<?php

session_start();

if (isset($_SESSION['matricule_user'])) {
    session_destroy();
    header("Location: /GestionDesActiviteEsp/index.php");
}
