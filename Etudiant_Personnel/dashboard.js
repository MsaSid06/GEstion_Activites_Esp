
let DataActivites =
  JSON.parse(localStorage.getItem("esp_activities")) || defaultActivities;

let currentFilter = "ALL";
let currentDept = "ALL";
let searchQuery = "";
let currentDate = new Date(2026, 5, 1);
let viewHistory = "dashboard"; // Retient d'où l'on vient pour le bouton retour

window.onload = function () {
  if (localStorage.getItem("user_name")) {
    let profile = localStorage.getItem("user_profil") || "Etudiant";
    profile = profile.charAt(0).toUpperCase() + profile.slice(1).toLowerCase();
    document.getElementById("user-full-name").innerText =
      localStorage.getItem("user_name") + " (" + profile + ")";
  }
  renderAll();
  renderCalendar();
  renderNotifications();
};

function clearNotifications() {
  listeNotifications = [];
  saveData();
  renderNotifications();
}

function simulateNewActivity() {
  const depts = [
    "Informatique",
    "Génie Civil",
    "Génie Électrique",
    "Direction Générale",
  ];
  const randomDept = depts[Math.floor(Math.random() * depts.length)];
  const randomId = DataActivites.length + 1;
  const nouvelleActivite = {
    id: randomId,
    titre: `Nouvelle activité du gestionnaire #${randomId}`,
    dept: randomDept,
    statut: "À venir",
    date: "28/06/2026",
    lieu: "Amphi ESP",
    desc: "Cette activité a été générée automatiquement pour simuler l'action de création d'un gestionnaire.",
  };
  DataActivites.push(nouvelleActivite);
  listeNotifications.unshift({
    id: Date.now(),
    message: `Le gestionnaire a ajouté : <strong>${nouvelleActivite.titre}</strong>.`,
    temps: "À l'instant",
  });
  saveData();
  renderAll();
  renderNotifications();
}

function renderCalendar() {
  const monthYearLabel = document.getElementById("calendar-month-year");
  const subTitleLabel = document.getElementById("dash-subtitle-date");
  const gridDays = document.getElementById("calendar-days-grid");
  const formattedDate = currentDate.toLocaleDateString("fr-FR", {
    month: "long",
    year: "numeric",
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
      daysHTML += `<div class="bg-purple-100 text-esp-purple rounded-full w-8 h-8 flex items-center justify-center mx-auto relative">${day}</div>`;
    } else if (day === 20 && month === 5 && year === 2026) {
      daysHTML += `<div class="bg-amber-100 text-amber-700 rounded-full w-8 h-8 flex items-center justify-center mx-auto relative">${day}</div>`;
    } else {
      daysHTML += `<div class="hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center mx-auto cursor-pointer">${day}</div>`;
    }
  }
  gridDays.innerHTML = daysHTML;
}

function changeMonth(dir) {
  currentDate.setMonth(currentDate.getMonth() + dir);
  renderCalendar();
}

function renderAll() {
  const containerDash = document.getElementById("dashboard-activities-list");
  const containerGrid = document.getElementById("grid-activities-container");
  let filtered = DataActivites.filter((act) => {
    const mapStatus =
      currentFilter === "ALL" ||
      (currentFilter === "AVENIR" && act.statut === "À venir") ||
      (currentFilter === "EN_COURS" && act.statut === "En cours") ||
      (currentFilter === "TERMINE" && act.statut === "Terminé");
    const matchDept = currentDept === "ALL" || act.dept === currentDept;
    const matchSearch =
      act.titre.toLowerCase().includes(searchQuery.toLowerCase()) ||
      act.desc.toLowerCase().includes(searchQuery.toLowerCase());
    return mapStatus && matchDept && matchSearch;
  });
  document.getElementById("counter-avenir").innerText = DataActivites.filter(
    (a) => a.statut === "À venir",
  ).length;
  document.getElementById("counter-encours").innerText = DataActivites.filter(
    (a) => a.statut === "En cours",
  ).length;
  document.getElementById("counter-termine").innerText = DataActivites.filter(
    (a) => a.statut === "Terminé",
  ).length;
  document.getElementById("activities-count").innerText =
    `${filtered.length} activités filtrées`;

  containerDash.innerHTML = filtered
    .map(
      (act) => `
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:shadow-md transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <span class="${act.statut === "À venir" ? "bg-purple-100 text-esp-purple" : act.statut === "En cours" ? "bg-amber-100 text-amber-700" : "bg-emerald-100 text-emerald-700"} text-[10px] font-bold px-2.5 py-1 rounded-full">● ${act.statut}</span>
                            <span class="text-xs font-medium text-gray-400">${act.dept}</span>
                        </div>
                        <h4 class="font-bold text-gray-900 text-base">${act.titre}</h4>
                    </div>
                    <button onclick="openDetailedView(${act.id})" class="btn-details-light font-bold text-xs px-5 py-2.5 rounded-full transition flex items-center gap-1.5 focus:outline-none">Détails</button>
                </div>
            `,
    )
    .join("");

  containerGrid.innerHTML = filtered
    .map(
      (act) => `
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="${act.statut === "À venir" ? "bg-purple-100 text-esp-purple" : act.statut === "En cours" ? "bg-amber-100 text-amber-700" : "bg-emerald-100 text-emerald-700"} text-[10px] font-bold px-3 py-1 rounded-full">● ${act.statut}</span>
                            <span class="text-xs font-semibold text-gray-400">${act.dept}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">${act.titre}</h3>
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">${act.desc}</p>
                    </div>
                    <button onclick="openDetailedView(${act.id})" class="w-full bg-esp-purple text-white font-bold text-xs py-3.5 rounded-xl transition">Voir les détails</button>
                </div>
            `,
    )
    .join("");
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
  const act = DataActivites.find((item) => item.id === id);
  if (!act) return;

  // Sauvegarder la vue actuelle pour pouvoir y retourner correctement
  if (document.getElementById("view-dashboard").classList.contains("block")) {
    viewHistory = "dashboard";
  } else {
    viewHistory = "list";
  }

  // Masquer les deux vues standards
  document.getElementById("view-dashboard").className = "space-y-8 hidden";
  document.getElementById("view-activities-list").className =
    "space-y-8 hidden";

  // Injecter les données dans l'interface détaillée
  document.getElementById("det-title").innerText = act.titre;
  document.getElementById("det-dept").innerText = act.dept;
  document.getElementById("det-desc").innerText = act.desc;
  document.getElementById("det-date").innerText = act.date;
  document.getElementById("det-date-fin").innerText = act.date;
  document.getElementById("det-lieu").innerText = act.lieu;

  // Personnalisation du badge de statut supérieur
  const badgeText = document.getElementById("det-badge-text");
  const badge = document.getElementById("det-badge");
  badgeText.innerText = act.statut;
  if (act.statut === "À venir") {
    badge.className =
      "bg-purple-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-purple-400/30";
  } else if (act.statut === "En cours") {
    badge.className =
      "bg-amber-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-amber-400/30";
  } else {
    badge.className =
      "bg-emerald-500/20 backdrop-blur-md text-white font-bold text-xs px-3.5 py-1 rounded-full flex items-center gap-1.5 border border-emerald-400/30";
  }

  // Afficher le bloc détaillé
  document.getElementById("view-activity-details").classList.remove("hidden");
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
}

function closeDetailedView() {
  document.getElementById("view-activity-details").classList.add("hidden");
  switchView(viewHistory);
}

function toggleNotifications(e) {
  e.stopPropagation();
  document.getElementById("notifications-dropdown").classList.toggle("hidden");
}
document.onclick = function () {
  document.getElementById("notifications-dropdown").classList.add("hidden");
};

function switchView(viewName) {
  // S'assurer que le bloc détails se ferme si on change de vue via le dock de navigation
  document.getElementById("view-activity-details").classList.add("hidden");

  const dash = document.getElementById("view-dashboard");
  const list = document.getElementById("view-activities-list");
  const btnDash = document.getElementById("btn-dock-dash");
  const btnList = document.getElementById("btn-dock-list");

  if (viewName === "dashboard") {
    dash.className = "space-y-8 block";
    list.className = "space-y-8 hidden";
    if (btnDash)
      btnDash.className = "p-2.5 bg-[#5D1962] rounded-full transition-all";
    if (btnList)
      btnList.className =
        "p-2.5 hover:bg-purple-900/40 rounded-full transition-all";
  } else {
    dash.className = "space-y-8 hidden";
    list.className = "space-y-8 block";
    if (btnList)
      btnList.className = "p-2.5 bg-[#5D1962] rounded-full transition-all";
    if (btnDash)
      btnDash.className =
        "p-2.5 hover:bg-purple-900/40 rounded-full transition-all";
  }
}
