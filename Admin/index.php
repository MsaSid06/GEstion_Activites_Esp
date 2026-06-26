<?php
/**
 * index.php
 * Tableau de bord : accessible uniquement aux utilisateurs connectés.
 * (Page volontairement simple : elle s'étoffera avec les écrans suivants.)
 */

require_once __DIR__ . '/includes/auth.php';
exiger_connexion();

$user = utilisateur_courant();

$libelles = [
    'ADMIN'        => 'Administrateur',
    'GESTIONNAIRE' => 'Gestionnaire d\'activités',
    'ETUDIANT'     => 'Étudiant',
    'PERSONNEL'    => 'Personnel',
];
$profilLisible = $libelles[$user['profil']] ?? $user['profil'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — ESP Dakar</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">

<header class="app-header">
    <div class="app-header-left">
        <div class="brand-logo">ESP</div>
        <strong>Planification des activités</strong>
    </div>
    <div class="app-user">
        <span><?= e($user['prenom']) ?> <?= e($user['nom']) ?></span>
        <span class="badge"><?= e($profilLisible) ?></span>
        <a class="app-logout" href="auth/logout.php">Se déconnecter</a>
    </div>
</header>

<main class="app-main">
    <div class="app-card">
        <h1>Bienvenue, <?= e($user['prenom']) ?> 👋</h1>
        <p>Vous êtes connecté en tant que <strong><?= e($profilLisible) ?></strong>
           (matricule <?= e($user['matricule']) ?>).</p>
        <p>Cette page est l'espace de travail. Les écrans suivants (calendrier,
           gestion des activités, administration des comptes…) viendront s'ajouter ici
           selon votre profil.</p>
    </div>
</main>

</body>
</html>
