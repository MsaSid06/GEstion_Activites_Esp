<?php
session_start();


require_once  '../config/connexion.php';
require_once  '../models/activite.php';

if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}




$pdo = connexionBD();

$id = (int) ($_GET['id'] ?? 0);
$activite = getActiviteParId($pdo, $id);

if (!$activite) {
    die("Activité introuvable");
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détails activité</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center p-6">

    <div
        class="bg-white rounded-2xl shadow-md p-5 border-l-[10px] border-transparent hover:border-l-purple-700 hover:shadow-xl hover:translate-x-1 transition-all duration-300 w-[600px]">

        <h1 class="text-2xl font-bold mb-4">
            <?= htmlspecialchars($activite['titre']) ?>

        </h1>

        <p class="mb-2 text-gray-700">
            <?= nl2br(htmlspecialchars($activite['description'])) ?>
        </p>

        <p class="mb-1"><strong>Type :</strong>
            <?= htmlspecialchars($activite['type_act']) ?>
        </p>
        <p class="mb-1"><strong>Début :</strong>
            <?= htmlspecialchars($activite['date_debut']) ?>
        </p>
        <p class="mb-1"><strong>Fin :</strong>
            <?= htmlspecialchars($activite['date_fin']) ?>
        </p>
        <p class="mb-4"><strong>Lieu 📍:</strong>
            <?= htmlspecialchars($activite['lieu']) ?>
        </p>

        <a href="./mesActivites.php"
            class="inline-block bg-esp-purple text-black px-4 py-2 rounded-xl text-sm font-semibold hover:bg-purple-900 hover:text-white transition ">
            <-- retour </a>

    </div>

</body>

</html>