<?php
session_start();
define("ROOT", "/GestionDesActiviteEsp");
require_once  './config/connexion.php';
require_once './models/activite.php';


if (!isset($_SESSION['matricule_user'])) {
    header("Location: /GestionDesActiviteEsp/index.php");
    exit;
}

$pdo = connexionBD();
$activites = getToutesActivitesGestionnaire($pdo, $_SESSION['matricule_user']);

// IMPORTANT : on définit la date du jour une seule fois, utilisée par toutes les fonctions
$dateDuJour = new DateTime();

function act_future()
{
    global $activites;
    global $dateDuJour;

    $activites_future = [];

    foreach ($activites as $a) {

        $dateDebut = new DateTime($a["date_debut"]);

        if ($dateDebut > $dateDuJour) {
            $activites_future[] = $a;
        }
    }

    return $activites_future;
}

function act_en_cour()
{
    global $activites;
    global $dateDuJour;

    $activites_en_cour = [];

    foreach ($activites as $a) {

        $dateDebut = new DateTime($a["date_debut"]);
        $dateFin = new DateTime($a["date_fin"]);

        if ($dateDebut <= $dateDuJour && $dateFin >= $dateDuJour) {
            $activites_en_cour[] = $a;
        }
    }

    return $activites_en_cour;
}

function act_terminer()
{
    global $activites;
    global $dateDuJour;

    $activites_fini = [];

    foreach ($activites as $a) {

        $dateFin = new DateTime($a["date_fin"]);

        if ($dateFin < $dateDuJour) {
            $activites_fini[] = $a;
        }
    }

    return $activites_fini;
}


function getStatutActivite(array $act, $dateDuJour)
{
    $dateDebut = new DateTime($act["date_debut"]);
    $dateFin   = new DateTime($act["date_fin"]);

    if ($dateFin < $dateDuJour) {
        return [
            'label'      => 'Terminée',
            'badgeClass' => 'text-green-600 bg-green-50',
            'dateClass'  => 'text-green-600',
            'icon'       => 'fa-solid fa-circle-check'
        ];
    } elseif ($dateDebut <= $dateDuJour && $dateFin >= $dateDuJour) {
        return [
            'label'      => 'En cours',
            'badgeClass' => 'text-orange-600 bg-orange-50',
            'dateClass'  => 'text-orange-600',
            'icon'       => 'fa-solid fa-hourglass-half'
        ];
    } else {
        return [
            'label'      => 'À venir',
            'badgeClass' => 'text-blue-600 bg-blue-50',
            'dateClass'  => 'text-blue-600',
            'icon'       => 'fa-solid fa-clock'
        ];
    }
}

// Liste des activités à afficher dans "Mes activités récentes"
// On trie par date de début (les plus récentes/proches en premier)
$activites_recentes = $activites;
usort($activites_recentes, function ($a, $b) {
    return strtotime($b["date_debut"]) - strtotime($a["date_debut"]);
});
$activites_recentes = array_slice($activites_recentes, 0, 3);

$events = [];

foreach ($activites as $a) {

    $events[] = [
        'title' => $a['titre'],
        'start' => $a['date_debut'],
        'end'   => $a['date_fin']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Activités</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="./assets/stylegest.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

    <header class="topbar bg-esp-purple">
        <div class="user">
            <div class="avatar">
                <?= strtoupper($_SESSION["prenom"][0])?>
            </div>
            <div>
                <h3> <?php echo $_SESSION["prenom"] . " ". strtoupper($_SESSION["nom"])?>
                </h3>
                <small>Gestionnaire</small>
                <a href="./controllers/logout.php"
                    class="flex items-center gap-2 text-red-500 hover:text-red-600 transition">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Déconnexion</span>

                </a>
            </div>
        </div>


        <a href="./action/formCreationActivite.php" class="flex items-center gap-2 bg-violet-600 text-white px-3 py-2 rounded-xl
              hover:bg-violet-700 transition">

            <i class="fa-solid fa-circle-plus"></i>
            <span class="text-sm font-medium">Creer une Activité</span>

        </a>
    </header>


    <main class="container">

        <!-- STATS -->
        <section class="stats">

            <div class="card blue">
                <i class="fa-solid fa-calendar-days"></i>
                <span id="upcoming"><?= count(act_future()) ?></span>
                À venir
            </div>

            <div class="card orange">
                <i class="fa-solid fa-hourglass-half"></i>
                <span id="ongoing"><?= count(act_en_cour()) ?></span>
                En cours
            </div>

            <div class="card gray">
                <i class="fa-solid fa-circle-check"></i>
                <span id="done"><?= count(act_terminer())?></span>
                Terminées
            </div>

        </section>
        <section class="content">

            <!-- CALENDRIER -->
            <div class="calendar-box">

                <!-- HEADER CALENDRIER : mois, année + "Activités du mois" -->
                <div class="calendar-header flex items-center justify-between mb-3">

                    <div>
                        <h3 id="monthYear" class="text-lg font-bold"></h3>
                        <small class="text-gray-500">Activités du mois</small>
                    </div>

                    <div class="flex gap-2">
                        <button id="prev" class="px-3 py-1 bg-gray-200 rounded">‹</button>
                        <button id="next" class="px-3 py-1 bg-gray-200 rounded">›</button>
                    </div>

                </div>

                <!-- CALENDRIER -->
                <div class="calendar-grid" id="calendar"></div>

                <!-- LÉGENDE -->
                <div class="legend mt-3 flex gap-4 text-sm">

                    <span><i class="dot blue"></i> À venir</span>
                    <span><i class="dot orange"></i> En cours</span>
                    <span><i class="dot green"></i> Terminées</span>

                </div>

            </div>
            <!-- ACTIVITÉS -->
            <div class="activities">

                <h3 class="text-lg font-semibold flex items-center justify-between mb-4">

                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-violet-600"></i>
                        Mes activités récentes
                    </span>

                    <a href="./action/mesActivites.php"
                        class="flex items-center gap-2 text-sm font-medium text-violet-600 hover:text-violet-800 transition">
                        Tout voir
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </h3>
                <?php if(count($activites) > 0):?>

                <div class="space-y-3">

                    <?php foreach ($activites_recentes as $act): ?>

                    <?php $statut = getStatutActivite($act, $dateDuJour); ?>

                    <div class="group bg-white border border-gray-100 rounded-xl p-4 shadow-sm
                        hover:shadow-lg hover:-translate-y-1 transition-all duration-300">

                        <!-- TAG -->
                        <div class="flex items-center justify-between mb-2">

                            <span
                                class="flex items-center gap-2 text-xs font-semibold <?= $statut['badgeClass'] ?> px-2 py-1 rounded-full">
                                <i
                                    class="<?= $statut['icon'] ?>"></i>
                                <?= $statut['label'] ?>
                            </span>

                            <i class="fa-regular fa-calendar text-gray-300 group-hover:text-violet-600 transition"></i>

                        </div>

                        <!-- TITRE -->
                        <h4
                            class="text-gray-800 font-semibold flex items-center gap-2 group-hover:text-violet-700 transition">

                            <i class="fa-solid fa-bullseye text-violet-500"></i>

                            <?= htmlspecialchars($act["titre"]) ?>

                        </h4>

                        <!-- DATE (colorée selon le statut) -->
                        <p
                            class="text-sm mt-1 flex items-center gap-2 font-medium <?= $statut['dateClass'] ?>">

                            <i class="fa-solid fa-clock-rotate-left"></i>

                            <?= $act["date_debut"] . " → " . $act["date_fin"] ?>

                        </p>

                        <!-- LIEU -->
                        <p class="text-sm text-gray-500 flex items-center gap-2 mt-1">

                            <i class="fa-solid fa-location-dot text-gray-400"></i>

                            <?= htmlspecialchars($act["lieu"]) ?>

                        </p>

                    </div>

                    <?php endforeach; ?>

                </div>
                <?php  else: ?>
                <div
                    class="bg-white p-6 rounded-xl shadow flex items-center gap-2 text-gray-500 hover:-translate-y-1 transition-all duration-300">
                    <i class="fa-regular fa-folder-open"></i>
                    Aucune activité disponible.
                </div>

                <?php endif; ?>

            </div>
            </div>


        </section>

    </main>

    <?php  include   "./action/footerGest.php" ?>

    <script src="./assets/scriptgest.js"></script>

    <script>
        const events = <?= json_encode($events) ?> ;

        let currentDate = new Date();

        const calendar = document.getElementById("calendar");
        const monthYear = document.getElementById("monthYear");

        function renderCalendar() {

            calendar.innerHTML = "";

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            const monthNames = [
                "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
            ];

            monthYear.innerText = monthNames[month] + " " + year;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                calendar.innerHTML += `<div></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {

                const cellDate = new Date(year, month, day);

                let className = "day";

                events.forEach(e => {

                    const start = new Date(e.start);
                    const end = new Date(e.end);

                    if (cellDate >= start && cellDate <= end) {

                        const now = new Date();

                        if (now < start) className += " upcoming";
                        else if (now >= start && now <= end) className += " ongoing";
                        else className += " done";
                    }
                });

                calendar.innerHTML += `<div class="${className}">${day}</div>`;
            }
        }

        document.getElementById("prev").onclick = () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        };

        document.getElementById("next").onclick = () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        };

        renderCalendar();
    </script>
</body>

</html>