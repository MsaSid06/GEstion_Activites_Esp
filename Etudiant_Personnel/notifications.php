<?php
session_start();
require_once '../Gestionnaire/config/connexion.php';
require_once '../Gestionnaire/models/notifications.php';


if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}


$pdo = connexionBD();
$notifications = getAllNotifications($pdo);
// var_dump($notifications[0]);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Notifications</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-gray-100">

    <section class="max-w-3xl mx-auto mt-8 px-4 space-y-5">

        <!-- HEADER -->
        <div class="flex items-center justify-between w-full mb-4">

            <!-- GAUCHE : titre + compteur -->
            <div class="flex items-center gap-2 text-gray-800 font-semibold text-lg">

                <i class="fa-solid fa-bell text-violet-600"></i>

                <span>Notifications</span>

                <span class="bg-gray-200 text-gray-700 text-sm px-2 py-0.5 rounded-full">
                    <?= count($notifications) ?>
                </span>

            </div>
            <a href="./dashboard_etd.php"
                class="flex items-center gap-2 text-gray-800 font-semibold text-lg hover:text-violet-600 rounded-full transition">

                <i class="fa-solid fa-arrow-left"></i>

                <span> Retour</span>

            </a>

        </div>
        <div class="md:col-span-4 flex gap-1 justify-end">
            <button onclick="filterGrid('ALL')"
                class="grid-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">Toutes</button>
            <button onclick="filterGrid('AVENIR')"
                class="grid-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">Notifications
                provenant des activitées de ma structure</button>
        </div>

        <!-- LISTE -->
        <div class="space-y-4">

            <?php foreach ($notifications as $notif): ?>

            <div id='<?= $notif['id_not'] ?>'
                class="bg-white shadow-md rounded-xl p-4 flex gap-3 items-start hover:shadow-lg transition">

                <!-- ICON -->
                <div
                    class="w-11 h-11 flex items-center justify-center rounded-full
                            <?= $notif['id_act'] ? 'bg-violet-100 text-violet-600' : 'bg-orange-100 text-orange-500' ?>">

                    <?php if ($notif['id_act']): ?>
                    <i class="fa-solid fa-calendar-check"></i>
                    <?php else: ?>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php endif; ?>

                </div>

                <!-- CONTENU -->
                <div class="flex-1">

                    <!-- MESSAGE -->
                    <p class="text-gray-800 font-medium">
                        <?= htmlspecialchars($notif['message']) ?>
                    </p>

                    <!-- INFOS -->
                    <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-3">

                        <!-- DATE -->
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-calendar-days"></i>
                            <?= $notif['date_envoi'] ?>
                        </span>

                        <!-- ACTIVITE -->
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-link"></i>
                            <?= $notif['activite'] ?? 'Aucune activité' ?>
                        </span>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center gap-2">


                    <button
                        onclick="removeNotif(<?= $notif['id_not'] ?>)"
                        type="button" class="w-9 h-9 flex items-center justify-center rounded-full
                       bg-red-100 text-red-600
                       hover:bg-red-200 transition">

                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <input style="display: none;" type="text"
                        value="<?= $notif['id_not'] ?>">

                </div>

            </div>

            <?php endforeach; ?>

            <?php if (empty($notifications)): ?>
            <div class="text-center text-gray-500 mt-10">
                <i class="fa-regular fa-bell-slash text-3xl"></i>
                <p class="mt-2">Aucune notification</p>
            </div>
            <?php endif; ?>

        </div>
    </section>
</body>
<script src="./notifications.js"></script>

</html>