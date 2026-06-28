<?php
session_start();
if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP Dakar - Application</title>
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
                    <button onclick="clearNotifications()"
                        class="text-xs text-esp-purple font-semibold hover:underline">Tout marquer lu</button>
                </div>
                <div id="notif-list" class="space-y-2.5 max-h-60 overflow-y-auto text-xs custom-scrollbar pr-1"></div>
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
            <a href="/GestionDesActiviteEsp/profil.php" title="Gestion du profil"
                class="relative flex items-center justify-center cursor-pointer">
                <div id="dock-avatar"
                    class="w-8 h-8 bg-[#322336] text-[#A697A8] text-xs font-bold rounded-full flex items-center justify-center border border-purple-800/40">
                    MC</div>
                <span
                    class="absolute bottom-0 right-0 w-2 h-2 bg-[#D4AF37] rounded-full border border-[#4A0E4E]"></span>
            </a>
        </div>
    </div>

    <script>
        const defaultActivities = [{
                id: 1,
                titre: "Journée Portes Ouvertes 2026",
                dept: "Direction Générale",
                statut: "À venir",
                date: "15/07/2026",
                lieu: "Campus principal",
                desc: "Présentation complète de l'école aux futurs bacheliers et familles."
            },
            {
                id: 2,
                titre: "Conférence Durable & Génie Civil",
                dept: "Génie Civil",
                statut: "En cours",
                date: "20/06/2026",
                lieu: "Amphithéâtre A",
                desc: "Conférence internationale sur l'usage des éco-matériaux."
            },
            {
                id: 3,
                titre: "Hackathon Intelligence Artificielle",
                dept: "Informatique",
                statut: "Terminé",
                date: "10/05/2026",
                lieu: "Lab Informatique",
                desc: "Compétition de codage intense de 48 heures."
            },
            {
                id: 4,
                titre: "Séminaire Énergies Renouvelables",
                dept: "Génie Électrique",
                statut: "À venir",
                date: "05/09/2026",
                lieu: "Salle B2",
                desc: "Atelier pratique sur les systèmes photovoltaïques."
            }
        ];

        const defaultNotifications = [{
            id: 1,
            message: "La <strong>Journée Portes Ouvertes 2026</strong> a été ajoutée.",
            temps: "Il y a 10 min"
        }];

        let DataActivites = JSON.parse(localStorage.getItem('esp_activities')) || defaultActivities;
        let listeNotifications = JSON.parse(localStorage.getItem('esp_notifications')) || defaultNotifications;

        let currentFilter = 'ALL';
        let currentDept = 'ALL';
        let searchQuery = '';
        let currentDate = new Date(2026, 5, 1);
        let viewHistory = 'dashboard'; // Retient d'où l'on vient pour le bouton retour

        window.onload = function() {
            if (localStorage.getItem('user_name')) {
                let profile = localStorage.getItem('user_profil') || 'Etudiant';
                profile = profile.charAt(0).toUpperCase() + profile.slice(1).toLowerCase();
                document.getElementById('user-full-name').innerText = localStorage.getItem('user_name') + " (" +
                    profile + ")";
            }
            renderAll();
            renderCalendar();
            updateDockAvatar();
            renderNotifications();
        };

        function saveData() {
            localStorage.setItem('esp_activities', JSON.stringify(DataActivites));
            localStorage.setItem('esp_notifications', JSON.stringify(listeNotifications));
        }

        function updateDockAvatar() {
            const nameText = document.getElementById('user-full-name').innerText;
            const cleanName = nameText.split('(')[0].trim();
            const words = cleanName.split(' ');
            let initials = words.length >= 2 ? words[0][0] + words[1][0] : words[0].substring(0, 2);
            document.getElementById('dock-avatar').innerText = initials.toUpperCase();
        }

        // COEUR MODIFICATION 1 : DYNAMISATION REELLE DES NOTIFICATIONS
        function renderNotifications() {
            const badge = document.getElementById('notif-badge');
            const containerNotif = document.getElementById('notif-list');
            if (listeNotifications.length > 0) {
                badge.innerText = listeNotifications.length;
                badge.classList.remove('hidden');
                containerNotif.innerHTML = listeNotifications.map(n => `
                    <div class="p-2 bg-purple-50 rounded-xl border border-purple-100/50">
                        <p class="text-gray-800 font-medium">${n.message}</p>
                        <span class="text-gray-400 text-[10px]">${n.temps}</span>
                    </div>
                `).join('');
            } else {
                badge.classList.add('hidden');
                containerNotif.innerHTML =
                    `<p class="text-gray-400 text-center py-4">Aucune nouvelle notification.</p>`;
            }
        }

        function clearNotifications() {
            listeNotifications = [];
            saveData();
            renderNotifications();
        }

        function simulateNewActivity() {
            const depts = ["Informatique", "Génie Civil", "Génie Électrique", "Direction Générale"];
            const randomDept = depts[Math.floor(Math.random() * depts.length)];
            const randomId = DataActivites.length + 1;
            const nouvelleActivite = {
                id: randomId,
                titre: `Nouvelle activité du gestionnaire #${randomId}`,
                dept: randomDept,
                statut: "À venir",
                date: "28/06/2026",
                lieu: "Amphi ESP",
                desc: "Cette activité a été générée automatiquement pour simuler l'action de création d'un gestionnaire."
            };
            DataActivites.push(nouvelleActivite);
            listeNotifications.unshift({
                id: Date.now(),
                message: `Le gestionnaire a ajouté : <strong>${nouvelleActivite.titre}</strong>.`,
                temps: "À l'instant"
            });
            saveData();
            renderAll();
            renderNotifications();
        }

        function renderCalendar() {
            const monthYearLabel = document.getElementById('calendar-month-year');
            const subTitleLabel = document.getElementById('dash-subtitle-date');
            const gridDays = document.getElementById('calendar-days-grid');
            const formattedDate = currentDate.toLocaleDateString('fr-FR', {
                month: 'long',
                year: 'numeric'
            });
            monthYearLabel.innerText = formattedDate;
            if (subTitleLabel) subTitleLabel.innerText = formattedDate;
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            let firstDayIndex = new Date(year, month, 1).getDay() - 1;
            if (firstDayIndex < 0) firstDayIndex = 6;
            const totalDays = new Date(year, month + 1, 0).getDate();
            const prevTotalDays = new Date(year, month, 0).getDate();
            let daysHTML = "";
            for (let i = firstDayIndex; i > 0; i--) {
                daysHTML += `<div class="text-gray-300">${prevTotalDays - i + 1}</div>`;
            }
            for (let day = 1; day <= totalDays; day++) {
                if (day === 15 && month === 5 && year === 2026) {
                    daysHTML +=
                        `<div class="bg-purple-100 text-esp-purple rounded-full w-8 h-8 flex items-center justify-center mx-auto relative">${day}</div>`;
                } else if (day === 20 && month === 5 && year === 2026) {
                    daysHTML +=
                        `<div class="bg-amber-100 text-amber-700 rounded-full w-8 h-8 flex items-center justify-center mx-auto relative">${day}</div>`;
                } else {
                    daysHTML +=
                        `<div class="hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center mx-auto cursor-pointer">${day}</div>`;
                }
            }
            gridDays.innerHTML = daysHTML;
        }

        function changeMonth(dir) {
            currentDate.setMonth(currentDate.getMonth() + dir);
            renderCalendar();
        }

        function renderAll() {
            const containerDash = document.getElementById('dashboard-activities-list');
            const containerGrid = document.getElementById('grid-activities-container');
            let filtered = DataActivites.filter(act => {
                const mapStatus = currentFilter === 'ALL' || (currentFilter === 'AVENIR' && act.statut ===
                    'À venir') || (currentFilter === 'EN_COURS' && act.statut === 'En cours') || (
                    currentFilter === 'TERMINE' && act.statut === 'Terminé');
                const matchDept = currentDept === 'ALL' || act.dept === currentDept;
                const matchSearch = act.titre.toLowerCase().includes(searchQuery.toLowerCase()) || act.desc
                    .toLowerCase().includes(searchQuery.toLowerCase());
                return mapStatus && matchDept && matchSearch;
            });
            document.getElementById('counter-avenir').innerText = DataActivites.filter(a => a.statut === 'À venir')
                .length;
            document.getElementById('counter-encours').innerText = DataActivites.filter(a => a.statut === 'En cours')
                .length;
            document.getElementById('counter-termine').innerText = DataActivites.filter(a => a.statut === 'Terminé')
                .length;
            document.getElementById('activities-count').innerText = `${filtered.length} activités filtrées`;

            containerDash.innerHTML = filtered.map(act => `
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:shadow-md transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <span class="${act.statut==='À venir'?'bg-purple-100 text-esp-purple':act.statut==='En cours'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'} text-[10px] font-bold px-2.5 py-1 rounded-full">● ${act.statut}</span>
                            <span class="text-xs font-medium text-gray-400">${act.dept}</span>
                        </div>
                        <h4 class="font-bold text-gray-900 text-base">${act.titre}</h4>
                    </div>
                    <button onclick="openDetailedView(${act.id})" class="btn-details-light font-bold text-xs px-5 py-2.5 rounded-full transition flex items-center gap-1.5 focus:outline-none">Détails</button>
                </div>
            `).join('');

            containerGrid.innerHTML = filtered.map(act => `
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="${act.statut==='À venir'?'bg-purple-100 text-esp-purple':act.statut==='En cours'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'} text-[10px] font-bold px-3 py-1 rounded-full">● ${act.statut}</span>
                            <span class="text-xs font-semibold text-gray-400">${act.dept}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">${act.titre}</h3>
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">${act.desc}</p>
                    </div>
                    <button onclick="openDetailedView(${act.id})" class="w-full bg-esp-purple text-white font-bold text-xs py-3.5 rounded-xl transition">Voir les détails</button>
                </div>
            `).join('');
        }

        function filterDashboard(st) {
            currentFilter = st;
            renderAll();
        }

        function filterGrid(st) {
            currentFilter = st;
            renderAll();
        }

        function filterDepartment(dp) {
            currentDept = dp;
            renderAll();
        }

        function searchActivities(v) {
            searchQuery = v;
            renderAll();
        }

        // COEUR MODIFICATION 2 : LOGIQUE D'AFFICHAGE DU STYLE DE DETAIL IMMERSIF (Type image_9e0bde.png)
        function openDetailedView(id) {
            const act = DataActivites.find(item => item.id === id);
            if (!act) return;

            // Sauvegarder la vue actuelle pour pouvoir y retourner correctement
            if (document.getElementById('view-dashboard').classList.contains('block')) {
                viewHistory = 'dashboard';
            } else {
                viewHistory = 'list';
            }

            // Masquer les deux vues standards
            document.getElementById('view-dashboard').className = "space-y-8 hidden";
            document.getElementById('view-activities-list').className = "space-y-8 hidden";

            // Injecter les données dans l'interface détaillée
            document.getElementById('det-title').innerText = act.titre;
            document.getElementById('det-dept').innerText = act.dept;
            document.getElementById('det-desc').innerText = act.desc;
            document.getElementById('det-date').innerText = act.date;
            document.getElementById('det-date-fin').innerText = act.date;
            document.getElementById('det-lieu').innerText = act.lieu;

            // Personnalisation du badge de statut supérieur
            const badgeText = document.getElementById('det-badge-text');
            const badge = document.getElementById('det-badge');
            badgeText.innerText = act.statut;
            if (act.statut === 'À venir') {
                badge.className =
                    "bg-purple-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-purple-400/30";
            } else if (act.statut === 'En cours') {
                badge.className =
                    "bg-amber-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-amber-400/30";
            } else {
                badge.className =
                    "bg-emerald-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-emerald-400/30";
            }

            // Afficher le bloc détaillé
            document.getElementById('view-activity-details').classList.remove('hidden');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function closeDetailedView() {
            document.getElementById('view-activity-details').classList.add('hidden');
            switchView(viewHistory);
        }

        function toggleNotifications(e) {
            e.stopPropagation();
            document.getElementById('notifications-dropdown').classList.toggle('hidden');
        }
        document.onclick = function() {
            document.getElementById('notifications-dropdown').classList.add('hidden');
        };

        function switchView(viewName) {
            // S'assurer que le bloc détails se ferme si on change de vue via le dock de navigation
            document.getElementById('view-activity-details').classList.add('hidden');

            const dash = document.getElementById('view-dashboard');
            const list = document.getElementById('view-activities-list');
            const btnDash = document.getElementById('btn-dock-dash');
            const btnList = document.getElementById('btn-dock-list');

            if (viewName === 'dashboard') {
                dash.className = "space-y-8 block";
                list.className = "space-y-8 hidden";
                if (btnDash) btnDash.className = "p-2.5 bg-[#5D1962] rounded-full transition-all";
                if (btnList) btnList.className = "p-2.5 hover:bg-purple-900/40 rounded-full transition-all";
            } else {
                dash.className = "space-y-8 hidden";
                list.className = "space-y-8 block";
                if (btnList) btnList.className = "p-2.5 bg-[#5D1962] rounded-full transition-all";
                if (btnDash) btnDash.className = "p-2.5 hover:bg-purple-900/40 rounded-full transition-all";
            }
        }
    </script>
</body>

</html>