<?php
session_start();
/**
 * admin/dashboard.php
 * Vue d'ensemble de l'administration. Réservé au profil ADMIN.
 */

require_once  __DIR__ . '/../includes/auth.php';
require_once __DIR__ . "/../config/database.php";
// $pdo = connexionBD();

// exiger_profil(['ADMIN']);
if (!isset($_SESSION['matricule_user'])) {
    header("Location: ../../index.php");
    exit;
}
$chartLabels = [];
$chartValues = [];

$nb_users = 0;
$nb_gestionnaires = 0;
$nb_structures = 0;
/* ---------- Statistiques globales ---------- */
$nb_users         = (int) $pdo->query('SELECT COUNT(*) FROM UTILISATEUR')->fetchColumn();
$nb_activites     = (int) $pdo->query('SELECT COUNT(*) FROM ACTIVITE')->fetchColumn();
$nb_gestionnaires = (int) $pdo->query('SELECT COUNT(DISTINCT matricule_user) FROM GESTIONNAIRE')->fetchColumn();
$nb_departements  = (int) $pdo->query("SELECT COUNT(*) FROM STRUCTURE WHERE type_struct = 'DEPARTEMENT'")->fetchColumn();
$nb_structures    = (int) $pdo->query('SELECT COUNT(*) FROM STRUCTURE')->fetchColumn();
$notif_count      = (int) $pdo->query('SELECT COUNT(*) FROM NOTIFICATION')->fetchColumn();

/* ---------- Activités par structure ----------
   La structure d'une activité est déduite de la structure de rattachement
   de son créateur (table APPARTENIR), que tout utilisateur possède. Cela
   garantit que chaque activité est comptée, une seule fois, sous sa structure. */
$sqlChart = "SELECT s.nom_struct AS nom, COUNT(DISTINCT a.id_act) AS nb
             FROM STRUCTURE s
             LEFT JOIN APPARTENIR ap ON ap.id_struct = s.id_struct
             LEFT JOIN ACTIVITE a     ON a.matricule_user = ap.matricule_user
             GROUP BY s.id_struct, s.nom_struct
             ORDER BY s.nom_struct";
$chartRows = $pdo->query($sqlChart)->fetchAll();

$chartLabels = array_map(fn ($r) => $r['nom'], $chartRows);
$chartValues = array_map(fn ($r) => (int) $r['nb'], $chartRows);

/* ---------- Activités récentes ---------- */
$sqlRecent = "SELECT a.id_act, a.titre, a.date_debut, a.date_fin,
                (SELECT s.nom_struct
                   FROM APPARTENIR ap JOIN STRUCTURE s ON s.id_struct = ap.id_struct
                  WHERE ap.matricule_user = a.matricule_user LIMIT 1) AS departement
              FROM ACTIVITE a
              ORDER BY a.date_debut DESC
              LIMIT 6";
$recentes = $pdo->query($sqlRecent)->fetchAll();

/**
 * Statut d'une activité déduit de ses dates.
 */
function statut_activite(string $debut, string $fin): array
{
    $now = time();
    $d = strtotime($debut);
    $f = strtotime($fin);
    if ($now < $d) {
        return ['a_venir', 'À venir'];
    }
    if ($now > $f) {
        return ['termine', 'Terminé'];
    }
    return ['en_cours', 'En cours'];
}

/* ---------- Variables pour le layout ---------- */
$page_active = 'dashboard';
$titre       = "Vue d'ensemble";
$sous_titre  = 'Toutes les structures · Année ' . date('Y');

include __DIR__ . '/../includes/header_admin.php';
?>

<!-- Cartes de statistiques -->
<section class="adm-stats">
    <div class="stat-card">
        <span
            class="stat-ico"><?= icone('users', 26) ?></span>
        <span
            class="stat-num"><?= number_format($nb_users, 0, ',', ' ') ?></span>
        <span class="stat-cap">Utilisateurs</span>
    </div>
    <div class="stat-card">
        <span
            class="stat-ico"><?= icone('calendar', 26) ?></span>
        <span
            class="stat-num"><?= number_format($nb_activites, 0, ',', ' ') ?></span>
        <span class="stat-cap">Activités totales</span>
    </div>
    <div class="stat-card">
        <span
            class="stat-ico"><?= icone('user-check', 26) ?></span>
        <span
            class="stat-num"><?= number_format($nb_gestionnaires, 0, ',', ' ') ?></span>
        <span class="stat-cap">Gestionnaires</span>
    </div>
    <div class="stat-card">
        <span
            class="stat-ico"><?= icone('building', 26) ?></span>
        <span
            class="stat-num"><?= number_format($nb_structures, 0, ',', ' ') ?></span>
        <span class="stat-cap">Structures</span>
    </div>
</section>

<!-- Graphique + cartes de navigation -->
<section class="adm-grid">
    <div class="panel-card">
        <div class="panel-head">
            <?= icone('chart-bar', 20) ?>
            <h2>Activités par structure</h2>
        </div>
        <div class="chart-wrap">
            <canvas id="chartDepartements"
                data-labels='<?= e(json_encode($chartLabels, JSON_UNESCAPED_UNICODE)) ?>'
                data-values='<?= e(json_encode($chartValues)) ?>'></canvas>
        </div>
    </div>

    <div class="nav-cards">
        <a class="nav-card" href="./utilisateurs.php">
            <span
                class="nav-ico"><?= icone('users', 24) ?></span>
            <span class="nav-text">
                <strong>Gestion des utilisateurs</strong>
                <span><?= number_format($nb_users, 0, ',', ' ') ?>
                    comptes</span>
            </span>
            <span
                class="nav-chev"><?= icone('chevron-right', 20) ?></span>
        </a>

        <a class="nav-card" href="./gestionnaires.php">
            <span
                class="nav-ico"><?= icone('user-check', 24) ?></span>
            <span class="nav-text">
                <strong>Gestion des gestionnaires</strong>
                <span><?= $nb_gestionnaires ?> actifs</span>
            </span>
            <span
                class="nav-chev"><?= icone('chevron-right', 20) ?></span>
        </a>

        <a class="nav-card" href="./structures.php">
            <span
                class="nav-ico"><?= icone('building', 24) ?></span>
            <span class="nav-text">
                <strong>Gestion des structures</strong>
                <span><?= $nb_structures ?> structures</span>
            </span>
            <span
                class="nav-chev"><?= icone('chevron-right', 20) ?></span>
        </a>
    </div>
</section>

<!-- Activités récentes -->
<section class="panel-card recent-card">
    <div class="panel-head">
        <h2>Activités récentes</h2>
    </div>

    <?php if (empty($recentes)): ?>
    <p class="recent-empty">Aucune activité enregistrée pour le moment.</p>
    <?php else: ?>
    <ul class="recent-list">
        <?php foreach ($recentes as $a):
            [$cls, $label] = statut_activite($a['date_debut'], $a['date_fin']); ?>
        <li class="recent-item">
            <div class="recent-info">
                <strong><?= e($a['titre']) ?></strong>
                <span>
                    <?= e($a['departement'] ?? 'Structure non renseignée') ?>
                    ·
                    <?= e(date('d/m/Y', strtotime($a['date_debut']))) ?>
                </span>
            </div>
            <span class="badge badge-<?= $cls ?>">
                <span class="badge-dot"></span><?= e($label) ?>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>