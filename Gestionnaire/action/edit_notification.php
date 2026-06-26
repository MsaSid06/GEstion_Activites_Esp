<?php
session_start();
require_once "../config/connexion.php";
require_once "../models/notifications.php";

$pdo = connexionBD();

if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_not = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (!$id_not) {
    header("Location: ./notifications.php");
    exit;
}

$notif = getNotificationParId($pdo, $id_not);

if (!$notif) {
    header("Location: ./notifications.php");
    exit;
}

$old = $notif;

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier Notification</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <main class="w-full max-w-2xl">

        <section class="bg-white p-6 rounded-2xl shadow">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-5">
                <i class="fa-solid fa-pen-to-square text-orange-500"></i>
                Modifier notification
            </h2>

            <form method="POST" action="./update_notification.php" class="space-y-4">

                <input type="hidden" name="id_not"
                    value="<?= $old['id_not'] ?>"
                    required>

                <!-- ID ACT -->
                <div>
                    <label class="text-sm font-medium">ID Activité</label>
                    <input type="number" name="id_act"
                        value="<?= e($old['id_act']) ?>"
                        class="w-full border rounded-lg px-3 py-2">
                </div>

                <!-- MESSAGE -->
                <div>
                    <label class="text-sm font-medium">Message</label>
                    <textarea required name="message" rows="4"
                        class="w-full border rounded-lg px-3 py-2"><?= e($old['message']) ?></textarea>
                </div>

                <div class="flex justify-end gap-3">

                    <a href="./Affiche_notif.php" class="px-4 py-2 border rounded-lg">
                        Annuler
                    </a>

                    <button class="bg-orange-500 text-white px-5 py-2 rounded-lg hover:bg-orange-600">
                        Modifier
                    </button>

                </div>

            </form>

        </section>

    </main>

</body>

</html>