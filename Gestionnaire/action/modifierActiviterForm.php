<?php
session_start();
require_once "../config/connexion.php";
require_once "../models/activite.php";


if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}



$id = $_GET['id'];
$mode = "";
$pdo  = connexionBD();

$activite = getActiviteParId($pdo, $id);

$old = $old ?? [
    'id_act'      => $id,
    'titre'       => $activite["titre"],
    'description' => $activite["description"],
    'type_act'    => 'AUTRE',
    'date_debut'  => $activite["date_debut"],
    'date_fin'    => $activite["date_fin"],
    'lieu'        => $activite["lieu"],
];
$errors = $errors ?? [];

// Doit correspondre à la contrainte CHECK (type_act) de la table ACTIVITE.
$TYPES = [
    'COURS'                   => 'Cours',
    'EXAMEN'                  => 'Examen',
    'SOUTENANCE'               => 'Soutenance',
    'REUNION'                  => 'Réunion',
    'FORMATION'                 => 'Formation',
    'SEMINAIRE'                 => 'Séminaire',
    'CONFERENCE'                => 'Conférence',
    'ATELIER'                   => 'Atelier',
    'COLLOQUE'                  => 'Colloque',
    'CEREMONIE'                 => 'Cérémonie',
    'JOURNEE_PORTES_OUVERTES'   => 'Journée portes ouvertes',
    'ACCUEIL_NOUVEAUX'          => 'Accueil des nouveaux',
    'ASSEMBLEE_GENERALE'        => 'Assemblée générale',
    'ELECTION'                  => 'Élection',
    'SORTIE_PEDAGOGIQUE'        => 'Sortie pédagogique',
    'VISITE'                    => 'Visite',
    'COMPETITION'               => 'Compétition',
    'ACTIVITE_CULTURELLE'       => 'Activité culturelle',
    'ACTIVITE_SPORTIVE'         => 'Activité sportive',
    'CAMPAGNE_SENSIBILISATION'  => 'Campagne de sensibilisation',
    'ACTION_SOCIALE'            => 'Action sociale',
    'FETE'                      => 'Fête',
    'PROJET'                    => 'Projet',
    'AUTRE'                     => 'Autre',
];

function e(string $valeur): string
{
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}


$estEdit = true;

// Classes communes pour les champs (texte, select, textarea).
$inputBase = 'w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 transition focus:outline-none focus:ring-2 focus:ring-offset-0';
function classeChamp(string $base, bool $enErreur): string
{
    return $base . ' ' . ($enErreur
        ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200'
        : 'border-gray-300 focus:border-[#5b2150] focus:ring-[#5b2150]/20');
}

// Petit helper pour afficher un message d'erreur PHP existant
// dans un <span> qui reste toujours présent dans le DOM (pour le JS).
function classeSpanErreur(bool $enErreur): string
{
    return 'text-xs text-rose-600' . ($enErreur ? '' : ' hidden');
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $estEdit ? "Modifier l'activité" : 'Nouvelle activité' ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        aubergine: {
                            50: '#f6eef4',
                            100: '#ecd9e6',
                            300: '#c98fb5',
                            600: '#7a2f63',
                            700: '#5b2150',
                            800: '#481a40',
                            900: '#371330',
                        },
                    },
                },
            },
        };
    </script>
</head>

<body class="min-h-screen bg-gray-50 py-10 px-4">

    <main class="mx-auto max-w-3xl">

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:p-8">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
                <?= $estEdit ? "Modifier l'activité" : 'Nouvelle activité' ?>
            </h2>

            <?php if (!empty($errors['global'])): ?>
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?= e($errors['global']) ?>
            </div>
            <?php endif; ?>

            <form id="formActivite" method="post" action="./save_modifAct.php" novalidate class="space-y-5">
                <input type="hidden" name="form_action" value="modifier">
                <?php if ($estEdit): ?>
                <input type="hidden" name="id_act"
                    value="<?= e((string) $old['id_act']) ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700" for="titre">Titre</label>
                        <input
                            class="<?= classeChamp($inputBase, isset($errors['titre'])) ?>"
                            type="text" id="titre" name="titre"
                            value="<?= e($old['titre']) ?>"
                            maxlength="100" required>
                        <span id="err-titre"
                            class="<?= classeSpanErreur(isset($errors['titre'])) ?>">
                            <?= e($errors['titre'] ?? '') ?>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700" for="lieu">Lieu</label>
                        <input
                            class="<?= classeChamp($inputBase, isset($errors['lieu'])) ?>"
                            type="text" id="lieu" name="lieu"
                            value="<?= e($old['lieu']) ?>"
                            maxlength="100" required>
                        <span id="err-lieu"
                            class="<?= classeSpanErreur(isset($errors['lieu'])) ?>">
                            <?= e($errors['lieu'] ?? '') ?>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700" for="type_act">Type d'activité</label>
                        <select
                            class="<?= classeChamp($inputBase, isset($errors['type_act'])) ?>"
                            id="type_act" name="type_act" required>
                            <?php foreach ($TYPES as $val => $lib): ?>
                            <option value="<?= $val ?>" <?= $old['type_act'] === $val ? 'selected' : '' ?>>
                                <?= e($lib) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span id="err-type_act"
                            class="<?= classeSpanErreur(isset($errors['type_act'])) ?>">
                            <?= e($errors['type_act'] ?? '') ?>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700" for="date_debut">Date de début</label>
                        <input
                            class="<?= classeChamp($inputBase, isset($errors['date_debut'])) ?>"
                            type="datetime-local" id="date_debut" name="date_debut"
                            value="<?= e($old['date_debut']) ?>"
                            required>
                        <span id="err-date_debut"
                            class="<?= classeSpanErreur(isset($errors['date_debut'])) ?>">
                            <?= e($errors['date_debut'] ?? '') ?>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700" for="date_fin">Date de fin</label>
                        <input
                            class="<?= classeChamp($inputBase, isset($errors['date_fin'])) ?>"
                            type="datetime-local" id="date_fin" name="date_fin"
                            value="<?= e($old['date_fin']) ?>"
                            required>
                        <span id="err-date_fin"
                            class="<?= classeSpanErreur(isset($errors['date_fin'])) ?>">
                            <?= e($errors['date_fin'] ?? '') ?>
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700" for="description">Description</label>
                    <textarea
                        class="<?= classeChamp($inputBase, isset($errors['description'])) ?> resize-none"
                        id="description" name="description" rows="4"
                        required><?= e($old['description']) ?></textarea>
                    <span id="err-description"
                        class="<?= classeSpanErreur(isset($errors['description'])) ?>">
                        <?= e($errors['description'] ?? '') ?>
                    </span>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="./mesActivites.php"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-aubergine-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-aubergine-800">
                        <?= 'Modifier' ?>
                    </button>
                </div>
            </form>
        </section>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('formActivite');

            // Liste des champs obligatoires, avec leur span d'erreur et leur message.
            var champs = [{
                    id: 'titre',
                    err: 'err-titre',
                    message: 'Le titre est requis.'
                },
                {
                    id: 'lieu',
                    err: 'err-lieu',
                    message: 'Le lieu est requis.'
                },
                {
                    id: 'type_act',
                    err: 'err-type_act',
                    message: "Le type d'activité est requis."
                },
                {
                    id: 'date_debut',
                    err: 'err-date_debut',
                    message: 'La date de début est requise.'
                },
                {
                    id: 'date_fin',
                    err: 'err-date_fin',
                    message: 'La date de fin est requise.'
                },
                {
                    id: 'description',
                    err: 'err-description',
                    message: 'La description est requise.'
                },
            ];

            function afficherErreur(champId, erreurId, message) {
                var input = document.getElementById(champId);
                var span = document.getElementById(erreurId);
                input.classList.add('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-200');
                input.classList.remove('border-gray-300', 'focus:border-[#5b2150]', 'focus:ring-[#5b2150]/20');
                span.textContent = message;
                span.classList.remove('hidden');
            }

            function masquerErreur(champId, erreurId) {
                var input = document.getElementById(champId);
                var span = document.getElementById(erreurId);
                input.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-200');
                input.classList.add('border-gray-300', 'focus:border-[#5b2150]', 'focus:ring-[#5b2150]/20');
                span.textContent = '';
                span.classList.add('hidden');
            }

            form.addEventListener('submit', function(e) {
                var valide = true;

                // 1. Vérifie que chaque champ obligatoire est rempli.
                champs.forEach(function(champ) {
                    var input = document.getElementById(champ.id);
                    if (!input.value.trim()) {
                        afficherErreur(champ.id, champ.err, champ.message);
                        valide = false;
                    } else {
                        masquerErreur(champ.id, champ.err);
                    }
                });

                // 2. Vérifie que date_fin >= date_debut (seulement si les deux sont remplies).
                var dateDebut = document.getElementById('date_debut').value;
                var dateFin = document.getElementById('date_fin').value;

                if (dateDebut && dateFin && new Date(dateFin) < new Date(dateDebut)) {
                    afficherErreur('date_fin', 'err-date_fin',
                        'La date de fin doit être postérieure ou égale à la date de début.');
                    valide = false;
                }

                if (!valide) {
                    e.preventDefault();
                }
            });

            // Bonus UX : empêche de choisir une date_fin avant la date_debut sélectionnée.
            document.getElementById('date_debut').addEventListener('change', function() {
                document.getElementById('date_fin').min = this.value;
            });
        });
    </script>

</body>

</html>