<?php
session_start();
require_once "../config/connexion.php";

if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}



$pdo = connexionBD();

$TYPES = [
    'COURS' => 'Cours',
    'EXAMEN' => 'Examen',
    'SOUTENANCE' => 'Soutenance',
    'REUNION' => 'Réunion',
    'FORMATION' => 'Formation',
    'SEMINAIRE' => 'Séminaire',
    'CONFERENCE' => 'Conférence',
    'ATELIER' => 'Atelier',
    'COLLOQUE' => 'Colloque',
    'CEREMONIE' => 'Cérémonie',
    'AUTRE' => 'Autre',
];

$old = $old ?? [
    'titre' => '',
    'description' => '',
    'type_act' => 'AUTRE',
    'date_debut' => '',
    'date_fin' => '',
    'lieu' => ''
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une activité</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        aubergine: {
                            700: '#5b2150',
                            800: '#481a40'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gray-50 py-10 px-4">

    <main class="mx-auto max-w-3xl">

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:p-8">

            <h2 class="mb-6 text-xl font-semibold text-gray-900">
                Nouvelle activité
            </h2>

            <?php if (!empty($errors['global'])): ?>
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?= e($errors['global']) ?>
            </div>
            <?php endif; ?>

            <form id="formActivite" method="post" action="./save_creation_activiter.php" class="space-y-5" novalidate>

                <input type="hidden" name="form_action" value="creer">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="text-sm font-medium">Titre de l’activité</label>
                        <input id="titre" name="titre"
                            value="<?= e($old['titre']) ?>"
                            class="w-full border rounded-lg px-3 py-2">
                        <span id="err-titre" class="text-red-500 text-xs"></span>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Lieu</label>
                        <input id="lieu" name="lieu"
                            value="<?= e($old['lieu']) ?>"
                            class="w-full border rounded-lg px-3 py-2">
                        <span id="err-lieu" class="text-red-500 text-xs"></span>
                    </div>

                </div>

                <div>
                    <label class="text-sm font-medium">Type d’activité</label>
                    <select id="type_act" name="type_act" class="w-full border rounded-lg px-3 py-2">

                        <?php foreach ($TYPES as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $old['type_act'] === $k ? 'selected' : '' ?>>
                            <?= e($v) ?>
                        </option>
                        <?php endforeach; ?>

                    </select>
                    <span id="err-type_act" class="text-red-500 text-xs"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="text-sm font-medium">Date de début</label>
                        <input type="datetime-local" id="date_debut" name="date_debut"
                            value="<?= e($old['date_debut']) ?>"
                            class="w-full border rounded-lg px-3 py-2">
                        <span id="err-date_debut" class="text-red-500 text-xs"></span>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Date de fin</label>
                        <input type="datetime-local" id="date_fin" name="date_fin"
                            value="<?= e($old['date_fin']) ?>"
                            class="w-full border rounded-lg px-3 py-2">
                        <span id="err-date_fin" class="text-red-500 text-xs"></span>
                    </div>

                </div>

                <div>
                    <label class="text-sm font-medium">Description</label>
                    <textarea id="description" name="description" class="w-full border rounded-lg px-3 py-2"
                        rows="4"><?= e($old['description']) ?></textarea>
                    <span id="err-description" class="text-red-500 text-xs"></span>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="./mesActivites.php" class="px-4 py-2 border rounded-lg">
                        Annuler
                    </a>

                    <button class="bg-aubergine-700 text-white px-5 py-2 rounded-lg">
                        Créer l’activité
                    </button>
                </div>

            </form>

        </section>

    </main>
    <?php  include "./footerGest.php" ?>


    <script>
        document.getElementById("formActivite").addEventListener("submit", function(e) {

            let ok = true;

            function err(id, msg) {
                document.getElementById("err-" + id).innerText = msg;
                ok = false;
            }

            function clear(id) {
                document.getElementById("err-" + id).innerText = "";
            }

            const champs = ["titre", "lieu", "type_act", "date_debut", "date_fin", "description"];

            champs.forEach(c => {
                const el = document.getElementById(c);
                if (!el.value.trim()) {
                    err(c, "Ce champ est obligatoire pour créer une activité.");
                } else {
                    clear(c);
                }
            });

            const d1 = new Date(date_debut.value);
            const d2 = new Date(date_fin.value);

            if (d2 < d1) {
                err("date_fin", "La date de fin doit être après la date de début.");
            }

            if (!ok) e.preventDefault();
        });
    </script>

</body>

</html>