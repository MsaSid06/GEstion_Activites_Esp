/**
 * assets/js/admin.js
 * Graphique "Activités par département" (Chart.js) : barres aubergine en
 * dégradé, coins arrondis, tooltip "Département activités : N".
 */
(function () {
    'use strict';

    var canvas = document.getElementById('chartDepartements');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    var labelsBruts = JSON.parse(canvas.dataset.labels || '[]');
    var valeurs     = JSON.parse(canvas.dataset.values || '[]');

    // Abréviation des noms longs (ex. "Departement Genie Informatique" -> "Departe.")
    function abreger(nom) {
        var court = nom.replace(/^D[ée]partement\s+/i, '').trim();
        return court.length > 9 ? court.slice(0, 8) + '.' : court;
    }
    var labels = labelsBruts.map(abreger);

    var ctx = canvas.getContext('2d');

    // Dégradé vertical aubergine.
    var gradient = ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 300);
    gradient.addColorStop(0, '#5a1240');
    gradient.addColorStop(1, '#2a0720');

    var maxVal = Math.max(1, Math.max.apply(null, valeurs.length ? valeurs : [1]));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: valeurs,
                backgroundColor: gradient,
                hoverBackgroundColor: '#3a0a2c',
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 46,
                categoryPercentage: 0.6,
                barPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#2a2230',
                    bodyColor: '#6a6470',
                    borderColor: '#ececef',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    titleFont: { family: 'Plus Jakarta Sans', weight: '700', size: 14 },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 13 },
                    callbacks: {
                        title: function (items) { return labelsBruts[items[0].dataIndex]; },
                        label: function (item) { return 'activités : ' + item.parsed.y; }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#9b909f', font: { family: 'Plus Jakarta Sans', size: 13 } }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: maxVal,
                    border: { display: false },
                    grid: { color: '#ececef', drawTicks: false, borderDash: [4, 4] },
                    ticks: {
                        color: '#9b909f',
                        font: { family: 'Plus Jakarta Sans', size: 12 },
                        precision: 0,
                        stepSize: Math.max(1, Math.ceil(maxVal / 4))
                    }
                }
            }
        }
    });
})();

/**
 * Volet de notifications (cloche) : ouverture/fermeture, "Tout lire", fermeture
 * au clic extérieur. Le "Tout lire" est cosmétique (pas d'état "lu" en base).
 */
(function () {
    'use strict';

    var bell  = document.getElementById('admBell');
    var panel = document.getElementById('admNotif');
    if (!bell || !panel) {
        return;
    }

    var btnClose   = document.getElementById('admNotifClose');
    var btnReadAll = document.getElementById('admNotifReadAll');
    var badge      = document.getElementById('admBellBadge');

    function ouvrir()  { panel.hidden = false; bell.setAttribute('aria-expanded', 'true'); }
    function fermer()  { panel.hidden = true;  bell.setAttribute('aria-expanded', 'false'); }
    function bascule() { panel.hidden ? ouvrir() : fermer(); }

    bell.addEventListener('click', function (e) { e.stopPropagation(); bascule(); });
    if (btnClose) { btnClose.addEventListener('click', fermer); }

    if (btnReadAll) {
        btnReadAll.addEventListener('click', function () {
            panel.querySelectorAll('.adm-notif-item').forEach(function (it) { it.classList.add('is-read'); });
            if (badge) { badge.style.display = 'none'; }
        });
    }

    // Fermer si on clique en dehors du volet.
    document.addEventListener('click', function (e) {
        if (!panel.hidden && !panel.contains(e.target) && !bell.contains(e.target)) {
            fermer();
        }
    });
    // Fermer avec Échap.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { fermer(); }
    });
})();

/**
 * Toast de confirmation : fermeture manuelle + disparition automatique.
 */
(function () {
    'use strict';
    var toast = document.getElementById('admToast');
    if (!toast) { return; }

    var x = toast.querySelector('.adm-toast-x');
    function cacher() { toast.style.display = 'none'; }

    if (x) { x.addEventListener('click', cacher); }
    setTimeout(cacher, 4000);
})();
