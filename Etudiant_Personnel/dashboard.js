let DataActivites = [];
let DataStructures = [];
let currentFilter = "ALL";
let currentDept = "ALL";
let searchQuery = "";
let currentDate = new Date();
let currentStructure = "ALL";
let viewHistory = "dashboard";

window.onload = async function () {
  await chargerStructures();
  await chargerActivites();
  renderAll();
  renderCalendar();
};
async function chargerStructures() {
  try {
    const res = await fetch("api_structures.php");
    const data = await res.json();

    if (!data.error) {
      DataStructures = data;
      remplirSelectStructures();
    } else {
      console.error("Erreur API structures :", data.error);
    }
  } catch (e) {
    console.error("Erreur chargement structures :", e);
  }
}

async function chargerActivites() {
  try {
    const res = await fetch("api_activites.php");
    const data = await res.json();
    console.log(data);

    if (!data.error) {
      DataActivites = data.map((act) => ({
        id: act.id_act,
        titre: act.titre,
        dept: act.nom_struct || "Non définie",
        type_struct: act.type_struct || "",
        type_act: act.type_act || "",
        statut: act.statut,
        date: formatDate(act.date_debut),
        date_fin: formatDate(act.date_fin),
        heure_debut: formatHeure(act.date_debut),
        heure_fin: formatHeure(act.date_fin),
        lieu: act.lieu,
        desc: act.description || "",
      }));
    }
  } catch (e) {
    console.error("Erreur chargement activités:", e);
  }
}

function formatDate(dateStr) {
  if (!dateStr) return "-";
  const d = new Date(dateStr);
  return d.toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function formatHeure(dateStr) {
  if (!dateStr) return "-";
  const d = new Date(dateStr);
  const h = String(d.getHours()).padStart(2, "0");
  const m = String(d.getMinutes()).padStart(2, "0");
  return `${h}h${m}`;
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

  const datesAvenir = DataActivites.filter((a) => a.statut === "À venir")
    .map((a) => {
      if (!a.date || a.date === "-") return null;
      const parts = a.date.split("/");
      const d = new Date(parts[2], parts[1] - 1, parts[0]);
      return d.getMonth() === month && d.getFullYear() === year
        ? d.getDate()
        : null;
    })
    .filter(Boolean);

  const datesEnCours = DataActivites.filter((a) => a.statut === "En cours")
    .map((a) => {
      if (!a.date || a.date === "-") return null;
      const parts = a.date.split("/");
      const d = new Date(parts[2], parts[1] - 1, parts[0]);
      return d.getMonth() === month && d.getFullYear() === year
        ? d.getDate()
        : null;
    })
    .filter(Boolean);

  const datesTermine = DataActivites.filter((a) => a.statut === "Terminé")
    .map((a) => {
      if (!a.date || a.date === "-") return null;
      const parts = a.date.split("/");
      const d = new Date(parts[2], parts[1] - 1, parts[0]);
      return d.getMonth() === month && d.getFullYear() === year
        ? d.getDate()
        : null;
    })
    .filter(Boolean);

  let daysHTML = "";
  for (let i = firstDayIndex; i > 0; i--) {
    daysHTML += `<div class="text-gray-300">${prevTotalDays - i + 1}</div>`;
  }
  for (let day = 1; day <= totalDays; day++) {
    const base =
      "rounded-full w-8 h-8 flex items-center justify-center mx-auto";
    if (datesEnCours.includes(day)) {
      daysHTML += `<div class="${base}" style="background:#fff0e0;color:#ff7a00">${day}</div>`;
    } else if (datesAvenir.includes(day)) {
      daysHTML += `<div class="${base}" style="background:#e6eff7;color:#0047ab">${day}</div>`;
    } else if (datesTermine.includes(day)) {
      daysHTML += `<div class="${base}" style="background:#e7f6ec;color:#16a34a">${day}</div>`;
    } else {
      daysHTML += `<div class="hover:bg-gray-100 ${base} cursor-pointer">${day}</div>`;
    }
  }
  gridDays.innerHTML = daysHTML;
}

function changeMonth(dir) {
  currentDate.setMonth(currentDate.getMonth() + dir);
  renderCalendar();
}

function updateFilterButtons(activeBtn, groupClass) {
  if (!activeBtn) return;
  document.querySelectorAll("." + groupClass).forEach((btn) => {
    btn.className = `${groupClass} bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-gray-50`;
  });
  activeBtn.className = `${groupClass} bg-esp-purple text-white px-5 py-2.5 rounded-full text-xs font-bold shadow-sm`;
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
    `${filtered.length} activité(s) trouvée(s)`;

  if (filtered.length === 0) {
    containerDash.innerHTML = `<p class="text-gray-400 text-center py-8">Aucune activité trouvée.</p>`;
    containerGrid.innerHTML = `<p class="text-gray-400 text-center py-8 col-span-2">Aucune activité trouvée.</p>`;
    return;
  }

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
                <p class="text-xs text-gray-400">${act.date} ${act.heure_debut} → ${act.date_fin} ${act.heure_fin}</p>
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
                <p class="text-xs text-gray-400">📅 ${act.date} ${act.heure_debut} → ${act.date_fin} ${act.heure_fin} | 📍 ${act.lieu}</p>
            </div>
            <button onclick="openDetailedView(${act.id})" class="w-full bg-esp-purple text-white font-bold text-xs py-3.5 rounded-xl transition">Voir les détails</button>
        </div>
    `,
    )
    .join("");
}

function filterDashboard(st, btn) {
  currentFilter = st;
  if (btn) updateFilterButtons(btn, "dash-filter-btn");
  renderAll();
}

function filterGrid(st, btn) {
  currentFilter = st;
  if (btn) updateFilterButtons(btn, "grid-filter-btn");
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

function openDetailedView(id) {
  const act = DataActivites.find((item) => item.id === id);
  if (!act) return;

  if (document.getElementById("view-dashboard").classList.contains("block")) {
    viewHistory = "dashboard";
  } else {
    viewHistory = "list";
  }

  document.getElementById("view-dashboard").className = "space-y-8 hidden";
  document.getElementById("view-activities-list").className =
    "space-y-8 hidden";

  document.getElementById("det-title").innerText = act.titre;
  document.getElementById("det-dept").innerText = act.dept;
  document.getElementById("det-desc").innerText = act.desc;
  document.getElementById("det-date").innerText = act.date;
  document.getElementById("det-heure-debut").innerText = act.heure_debut;
  document.getElementById("det-date-fin").innerText = act.date_fin;
  document.getElementById("det-heure-fin").innerText = act.heure_fin;
  document.getElementById("det-lieu").innerText = act.lieu;
  document.getElementById("det-type").innerText = act.type_act;

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

  document.getElementById("view-activity-details").classList.remove("hidden");
  window.scrollTo({ top: 0, behavior: "smooth" });
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
  document.getElementById("view-activity-details").classList.add("hidden");
  const dash = document.getElementById("view-dashboard");
  const list = document.getElementById("view-activities-list");
  const btnDash = document.getElementById("btn-dock-dash");
  const btnList = document.getElementById("btn-dock-list");

  if (viewName === "dashboard") {
    dash.className = "space-y-8 block";
    list.className = "space-y-8 hidden";
    setDockActif(btnDash, true);
    setDockActif(btnList, false);
  } else {
    dash.className = "space-y-8 hidden";
    list.className = "space-y-8 block";
    setDockActif(btnList, true);
    setDockActif(btnDash, false);
  }
}

// Deplace le rond clair (onglet actif) sur le bon bouton de la barre flottante.
function setDockActif(btn, actif) {
  if (!btn) return;
  btn.style.background = actif ? "rgba(255,255,255,.18)" : "transparent";
  btn.style.color = actif ? "#fff" : "rgba(255,255,255,.72)";
}
function remplirSelectStructures() {
  const select = document.getElementById("structure-filter");
  if (!select) return;

  select.innerHTML = `<option value="ALL">Toutes les structures</option>`;

  DataStructures.forEach((struct) => {
    const option = document.createElement("option");
    option.value = struct.nom_struct;
    option.textContent = `${struct.nom_struct} (${struct.type_struct})`;
    select.appendChild(option);
  });
}
