<?php
session_start();
require_once '../Gestionnaire/models/notifications.php';
require_once '../Gestionnaire/config/connexion.php';
if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}
$pdo = connexionBD();
$notifications = getAllNotifications($pdo);
// $activites = getToutesActivites($pdo);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP Dakar - Tableau de bord
        <?=  ($_SESSION['profil'] === 'ETUDIANT') ? "ETUDIANT" : "PERSONNEL";  ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-esp-purple {
            background-color: #4A0E4E;
        }

        .text-esp-purple {
            color: #4A0E4E;
        }

        .border-esp-purple {
            border-color: #4A0E4E;
        }

        .bg-esp-gold {
            background-color: #D4AF37;
        }

        .bg-light-gray {
            background-color: #FDFBFD;
        }

        .btn-details-light {
            background-color: #F3EBF4;
            color: #4A0E4E;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-light-gray min-h-screen font-sans antialiased pb-32">

    <header
        class="bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-esp-purple rounded-full flex items-center justify-center text-white font-black text-xs tracking-wider">
                ESP</div>
            <div>
                <h1 class="text-sm font-bold text-gray-900">
                    <?= strtoupper($_SESSION['nom']) . ' ' . strtoupper($_SESSION['prenom'])?>
                </h1>
                <p id="user-full-name" class="text-xs text-gray-500 font-medium">
                    <?= strtoupper($_SESSION['profil'])."(E)"?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-6 relative">
            <button onclick="toggleNotifications(event)"
                class="relative text-gray-400 hover:text-gray-600 transition focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span id="notif-badge"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center hidden">0</span>
            </button>

            <div id="notifications-dropdown"
                class="absolute right-0 top-10 bg-white border border-gray-100 w-80 rounded-2xl shadow-xl p-4 hidden space-y-3 z-50">
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="font-bold text-sm text-gray-800">Notifications</span>
                    <a href="./notifications.php" class="text-xs text-esp-purple font-semibold hover:underline">Voir
                        tout</a>
                </div>
                <div id="notif-list" class="space-y-2.5 max-h-60 overflow-y-auto text-xs custom-scrollbar pr-1">'
                    <?php foreach ($notifications as $n) {
                    }?>
                    <div class="p-2 bg-purple-50 rounded-xl border border-purple-100/50">
                        <p class="text-gray-800 font-medium">
                            <?=  $n['message']?>
                        </p>
                        <span
                            class="text-gray-400 text-[10px]"><?=  $n['date_envoi']?></span>
                    </div>

                </div>
            </div>


            <a href="/GestionDesActiviteEsp/Gestionnaire/controllers/logout.php"
                class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span class="hidden sm:inline">Déconnexion</span>

            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section id="view-dashboard" class="space-y-8 block">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Tableau de bord</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Activités de l'ESP — <span
                        id="dash-subtitle-date">Juin 2026</span></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div onclick="filterDashboard('AVENIR')"
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between min-h-[140px] cursor-pointer hover:shadow-md transition">
                    <span id="counter-avenir" class="text-5xl font-black text-esp-purple">0</span>
                    <span class="text-sm font-semibold text-gray-500">À venir</span>
                </div>
                <div onclick="filterDashboard('EN_COURS')"
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between min-h-[140px] cursor-pointer hover:shadow-md transition">
                    <span id="counter-encours" class="text-5xl font-black text-amber-500">0</span>
                    <span class="text-sm font-semibold text-gray-500">En cours</span>
                </div>
                <div onclick="filterDashboard('TERMINE')"
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between min-h-[140px] cursor-pointer hover:shadow-md transition">
                    <span id="counter-termine" class="text-5xl font-black text-emerald-600">0</span>
                    <span class="text-sm font-semibold text-gray-500">Terminées</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <span id="calendar-month-year" class="font-bold text-gray-900 text-base capitalize">Juin
                            2026</span>
                        <div class="flex items-center gap-3 text-gray-400">
                            <button onclick="changeMonth(-1)"
                                class="hover:text-gray-700 font-bold text-lg p-1 transition select-none">‹</button>
                            <button onclick="changeMonth(1)"
                                class="hover:text-gray-700 font-bold text-lg p-1 transition select-none">›</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-y-4 text-center text-xs font-bold text-gray-400 mb-2">
                        <div>Lu</div>
                        <div>Ma</div>
                        <div>Me</div>
                        <div>Je</div>
                        <div>Ve</div>
                        <div>Sa</div>
                        <div>Di</div>
                    </div>
                    <div id="calendar-days-grid"
                        class="grid grid-cols-7 gap-y-3 text-center text-sm font-semibold text-gray-700"></div>
                </div>

                <div class="lg:col-span-8 space-y-4">
                    <div
                        class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <div class="relative w-full sm:w-72 flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                                </svg>
                            </span>
                            <input type="text" oninput="searchActivities(this.value)"
                                placeholder="Rechercher une activité..."
                                class="w-full pl-11 pr-4 py-2.5 bg-[#FDFBFD] rounded-xl text-sm outline-none border border-gray-100 focus:ring-1 focus:ring-purple-900">
                        </div>
                        <div class="flex gap-1 overflow-x-auto w-full sm:w-auto">
                            <button onclick="filterDashboard('ALL')"
                                class="dash-filter-btn bg-esp-purple text-white px-5 py-2.5 rounded-full text-xs font-bold shadow-sm">Tout</button>
                            <button onclick="filterDashboard('AVENIR')"
                                class="dash-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">À
                                venir</button>
                            <button onclick="filterDashboard('EN_COURS')"
                                class="dash-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">En
                                cours</button>
                            <button onclick="filterDashboard('TERMINE')"
                                class="dash-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">Terminé</button>
                        </div>
                    </div>
                    <div id="dashboard-activities-list" class="space-y-4"></div>
                </div>
            </div>
        </section>

        <section id="view-activities-list" class="space-y-8 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Liste des activités</h2>
                    <p id="activities-count" class="text-sm text-gray-500 font-medium mt-1">0 activités enregistrées</p>
                </div>
                <button onclick="switchView('dashboard')"
                    class="bg-white border border-gray-200 text-gray-700 font-bold text-xs px-4 py-2.5 rounded-xl hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
                    <span>‹</span> Tableau de bord
                </button>
            </div>

            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-4 relative flex items-center">
                    <span class="absolute left-4 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                        </svg>
                    </span>
                    <input type="text" oninput="searchActivities(this.value)" placeholder="Rechercher..."
                        class="w-full pl-11 pr-4 py-3 bg-[#EFE5F0] text-gray-800 text-sm rounded-xl outline-none border-none placeholder-gray-500 font-medium">
                </div>
                <div class="md:col-span-4">
                    <select onchange="filterDepartment(this.value)"
                        class="w-full px-4 py-3 bg-[#EFE5F0] text-gray-800 font-semibold rounded-xl text-sm outline-none border-none cursor-pointer">
                        <option value="ALL">Tous les départements</option>
                        <option value="Direction Générale">Direction Générale</option>
                        <option value="Génie Civil">Génie Civil</option>
                        <option value="Informatique">Informatique</option>
                        <option value="Génie Électrique">Génie Électrique</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-1 justify-end">
                    <button onclick="filterGrid('ALL')"
                        class="grid-filter-btn bg-esp-purple text-white px-5 py-2.5 rounded-full text-xs font-bold shadow-sm">Tout</button>
                    <button onclick="filterGrid('AVENIR')"
                        class="grid-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">À
                        venir</button>
                    <button onclick="filterGrid('EN_COURS')"
                        class="grid-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">En
                        cours</button>
                    <button onclick="filterGrid('TERMINE')"
                        class="grid-filter-btn bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50">Terminé</button>
                </div>
            </div>
            <div id="grid-activities-container" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
        </section>

        <section id="view-activity-details" class="max-w-4xl mx-auto hidden space-y-6">
            <button onclick="closeDetailedView()"
                class="flex items-center space-x-2 text-gray-500 hover:text-esp-purple transition group mb-2">
                <span
                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center group-hover:border-esp-purple transition">‹</span>
                <span class="text-sm font-semibold">Retour aux activités</span>
            </button>

            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-esp-purple p-8 md:p-10 text-white space-y-4">
                    <div class="flex items-center space-x-3">
                        <span id="det-badge"
                            class="bg-white/20 backdrop-blur-md text-white font-semibold text-xs px-3 py-1 rounded-full flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-white"></span> <span id="det-badge-text">Statut</span>
                        </span>
                        <span id="det-dept" class="text-white/60 text-xs font-medium tracking-wide">Département</span>
                    </div>
                    <h2 id="det-title" class="text-2xl md:text-3xl font-black tracking-tight">Titre de l'activité</h2>
                </div>

                <div class="p-8 md:p-10 space-y-8">
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Description</h4>
                        <p id="det-desc" class="text-gray-700 leading-relaxed font-medium text-sm md:text-base">
                            Description complète...</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-[#F0E6F0]/60 border border-purple-100/30 p-4 rounded-2xl">
                            <span class="text-xs font-bold text-purple-400 block mb-0.5">Date de début</span>
                            <span id="det-date" class="text-sm md:text-base font-bold text-gray-800">-</span>
                        </div>
                        <div class="bg-[#F0E6F0]/60 border border-purple-100/30 p-4 rounded-2xl">
                            <span class="text-xs font-bold text-purple-400 block mb-0.5">Date de fin</span>
                            <span id="det-date-fin" class="text-sm md:text-base font-bold text-gray-800">-</span>
                        </div>
                        <div class="bg-[#F0E6F0]/60 border border-purple-100/30 p-4 rounded-2xl">
                            <span class="text-xs font-bold text-purple-400 block mb-0.5">Lieu</span>
                            <span id="det-lieu" class="text-sm md:text-base font-bold text-gray-800">-</span>
                        </div>
                        <div class="bg-[#F0E6F0]/60 border border-purple-100/30 p-4 rounded-2xl">
                            <span class="text-xs font-bold text-purple-400 block mb-0.5">Responsable</span>
                            <span id="det-responsable" class="text-sm md:text-base font-bold text-gray-800">Non
                                désigné</span>
                        </div>
                    </div>

                    <div class="bg-[#FFFDF5] border border-amber-200/50 p-5 rounded-2xl space-y-1">
                        <span class="text-xs font-bold text-amber-600 block">Ressources affectées</span>
                        <p id="det-ressources" class="text-sm md:text-base font-bold text-gray-800">Salle polyvalente,
                            matériel pédagogique standard</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 w-auto max-w-md">
        <div
            class="bg-[#4A0E4E] text-white rounded-full shadow-xl px-4 py-2 flex items-center gap-4 border border-purple-950/40">
            <button id="btn-dock-dash" onclick="switchView('dashboard')"
                class="p-2.5 bg-[#5D1962] rounded-full transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
            </button>
            <button id="btn-dock-list" onclick="switchView('list')"
                class="p-2.5 hover:bg-purple-900/40 rounded-full transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 text-purple-300">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.25 6.75h12M8.25 12h12m-12 5.75h12M3.75 6.75h.007v.008H3.75V6.75zm0 5.25h.007v.008H3.75V12zm0 5.75h.007v.008H3.75v-.008z" />
                </svg>
            </button>
            <div class="h-6 w-[1px] bg-purple-900/60"></div>
            <div class="relative flex items-center justify-center">
                <div id="dock-avatar"
                    class="w-8 h-8 bg-[#322336] text-[#A697A8] text-xs font-bold rounded-full flex items-center justify-center border border-purple-800/40">
                    <?= strtoupper($_SESSION['nom'][0]). strtoupper($_SESSION['prenom'][0]) ?>
                </div>
                <span class="absolute bottom-0 right-0 w-2 h-2 bg-[#D4AF37] rounded-full border border-[#4A0E4E]">

                </span>
            </div>
        </div>
    </div>

</body>
<script src="./dashboard.js"></script>

</html>