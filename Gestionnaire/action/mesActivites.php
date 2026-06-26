<?php
session_start();

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../models/activite.php';

if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}

$pdo = connexionBD();
$activites = getToutesActivitesGestionnaire($pdo, $_SESSION["matricule_user"]);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Activités ESP</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen font-sans">

    <div class="max-w-6xl mx-auto p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">

            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-violet-600"></i>
                Toutes mes activités
            </h1>

            <p class="text-sm text-gray-600 flex items-center gap-2">
                <i class="fa-solid fa-hashtag text-gray-400"></i>
                <?= count($activites) ?> activité(s)
            </p>

            <a href="../DashboardGestionnaire.php" class="flex items-center gap-2 bg-violet-600 text-white px-4 py-2 rounded-xl text-sm font-semibold
                  hover:bg-violet-700 transition">

                <i class="fa-solid fa-arrow-left"></i>
                Retour
            </a>

        </div>

        <!-- GRID -->
        <?php if (empty($activites)): ?>

        <div class="bg-white p-6 rounded-xl shadow flex items-center gap-2 text-gray-500">
            <i class="fa-regular fa-folder-open"></i>
            Aucune activité disponible.
        </div>

        <?php else: ?>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">

            <?php foreach ($activites as $a): ?>

            <div class="bg-white rounded-2xl shadow-md p-5 border-l-4 border-transparent
                            hover:border-violet-600 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <!-- TITRE + ACTIONS -->
                <div class="flex items-center justify-between mb-3">

                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">

                        <i class="fa-solid fa-bullseye text-violet-500"></i>

                        <?= htmlspecialchars($a['titre']) ?>

                    </h2>

                    <div class="flex items-center gap-2">

                        <!-- MODIFIER -->
                        <a href="./modifierActiviterForm.php?id=<?= $a['id_act'] ?>"
                            class="w-9 h-9 flex items-center justify-center rounded-lg
                                      bg-green-50 text-green-600
                                      hover:bg-green-100 hover:scale-105 transition">

                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <!-- SUPPRIMER -->
                        <form method="post" action="./save_suppression_activite.php"
                            onsubmit="return confirm('Supprimer cette activité ?');">

                            <input type="hidden" name="id_act"
                                value="<?= $a['id_act'] ?>">

                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg
                                               bg-red-50 text-red-600
                                               hover:bg-red-100 hover:scale-105 transition">

                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </form>

                    </div>

                </div>

                <!-- TYPE -->
                <p class="text-sm text-gray-600 flex items-center gap-2 mb-2">

                    <i class="fa-solid fa-tag text-gray-400"></i>

                    <?= htmlspecialchars($a['type_act']) ?>

                </p>

                <!-- DATE -->
                <p class="text-sm text-gray-600 flex items-center gap-2 mb-2">

                    <i class="fa-solid fa-clock text-gray-400"></i>

                    Début :
                    <?= htmlspecialchars($a['date_debut']) ?>

                </p>

                <!-- LIEU -->
                <p class="text-sm text-gray-600 flex items-center gap-2 mb-4">

                    <i class="fa-solid fa-location-dot text-gray-400"></i>

                    <?= htmlspecialchars($a['lieu']) ?>

                </p>

                <!-- BOUTON -->
                <a href="./detailsActivite.php?id=<?= $a['id_act'] ?>"
                    class="inline-flex items-center gap-2 bg-violet-600 text-white px-4 py-2 rounded-xl text-sm font-semibold
                              hover:bg-violet-700 transition">

                    <i class="fa-solid fa-eye"></i>
                    Voir détails

                </a>

            </div>

            <?php endforeach; ?>

        </div>

        <?php endif; ?>

    </div>

    <?php include "./footerGest.php"; ?>

</body>

</html>