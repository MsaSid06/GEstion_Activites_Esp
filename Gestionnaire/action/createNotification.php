<?php
session_start();


require_once  "../config/connexion.php";

if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}

$pdo = connexionBD();

$old = $old ?? [
    'id_act' => '',
    'message' => ''
];

$errors = $errors ?? [];

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Créer Notification</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        aubergine: {
                            700: '#5b2150'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gray-50 py-10 px-4">

    <main class="mx-auto max-w-3xl">

        <section class="rounded-2xl bg-white border border-gray-200 shadow-sm p-6 md:p-8">

            <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2 mb-6">
                <i class="fa-solid fa-bell text-violet-600"></i>
                Créer une notification
            </h2>

            <?php if (!empty($errors['global'])): ?>
            <div class="mb-4 bg-red-50 text-red-600 border border-red-200 px-4 py-3 rounded-lg text-sm">
                <?= e($errors['global']) ?>
            </div>
            <?php endif; ?>

            <form id="formNotif" method="post" action="./save_notification.php" class="space-y-5">

                <!-- ACTIVITY (optionnel) -->
                <div>
                    <label class="text-sm font-medium">ID Activité (optionnel)</label>
                    <input type="number" id="id_act" name="id_act"
                        value="<?= e($old['id_act']) ?>"
                        class="w-full border rounded-lg px-3 py-2" required>
                    <span id="err-id_act" class="text-red-500 text-xs"></span>
                </div>

                <!-- MESSAGE -->
                <div>
                    <label class="text-sm font-medium">Message</label>
                    <textarea required id="message" name="message" rows="4"
                        class="w-full border rounded-lg px-3 py-2"><?= e($old['message']) ?></textarea>
                    <span id="err-message" class="text-red-500 text-xs"></span>
                </div>

                <!-- ACTIONS -->
                <div class="flex justify-end gap-3 pt-4">

                    <a href="./affiche_notif.php" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Annuler
                    </a>

                    <button class="bg-aubergine-700 text-white px-5 py-2 rounded-lg hover:bg-violet-800 transition">
                        Envoyer
                    </button>

                </div>

            </form>

        </section>

    </main>
    <?php include  "./footerGest.php" ?>

</body>

</html>