<?php
/**
 * includes/footer_admin.php
 * Barre de navigation flottante (commune) + fermeture du document.
 * Utilise $page_active pour mettre en évidence l'onglet courant.
 */
$page_active = $page_active ?? '';
$user        = utilisateur_courant();
$initiales   = strtoupper(mb_substr($_SESSION['prenom'], 0, 1) . mb_substr($_SESSION['nom'], 0, 1));

$onglets = [
    'dashboard'     => ['dashboard.php',     'grid',       'Vue d\'ensemble'],
    'utilisateurs'  => ['utilisateurs.php',  'users',      'Utilisateurs'],
    'gestionnaires' => ['gestionnaires.php', 'user-check', 'Gestionnaires'],
    'structures'    => ['structures.php',   'building',   'Structures'],
];
?>
</main>

<nav class="adm-nav" aria-label="Navigation administration">
    <?php foreach ($onglets as $cle => $o): ?>
        <a href="<?= e($o[0]) ?>"
           class="adm-nav-item <?= $page_active === $cle ? 'is-active' : '' ?>"
           aria-label="<?= e($o[2]) ?>"
           <?= $page_active === $cle ? 'aria-current="page"' : '' ?>>
            <?= icone($o[1], 22) ?>
        </a>
    <?php endforeach; ?>
    <span class="adm-nav-sep" aria-hidden="true"></span>
    <span class="adm-nav-avatar" title="<?= e($user['prenom'] . ' ' . $user['nom']) ?>">
        <?= e($initiales) ?>
        <span class="adm-nav-dot" aria-hidden="true"></span>
    </span>
</nav>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
