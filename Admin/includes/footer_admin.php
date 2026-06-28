<?php
/**
 * includes/footer_admin.php
 * Fin de page admin + barre de navigation flottante (autonome, aubergine),
 * de MÊME taille et MÊME couleur que celle du Gestionnaire et de l'Étudiant.
 */
if (session_status() === PHP_SESSION_NONE && !headers_sent()) session_start();

$BASE   = '/GestionDesActiviteEsp';
$page   = basename(parse_url($_SERVER['PHP_SELF'] ?? '', PHP_URL_PATH));
$prenom = $_SESSION['user']['prenom'] ?? ($_SESSION['prenom'] ?? '');
$init   = $prenom !== '' ? mb_strtoupper(mb_substr($prenom, 0, 1)) : '';

$svg = function (string $n): string {
    $p = [
        'home'  => '<path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>',
        'users' => '<path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
        'plus'  => '<path d="M12 4.5v15m7.5-7.5h-15"/>',
        'build' => '<path d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>',
        'gest'  => '<path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0113.227-4.808"/><path d="M15.75 18.75l1.8 1.8 3.45-3.6"/>',
    ];
    $sw = $n === 'plus' ? '2.2' : '1.8';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $sw
         . '" stroke-linecap="round" stroke-linejoin="round">' . ($p[$n] ?? '') . '</svg>';
};

$items = [
    ['icon' => 'home',  'href' => "$BASE/Admin/admin/dashboard.php",                 'page' => 'dashboard.php',    'title' => 'Tableau de bord'],
    ['icon' => 'users', 'href' => "$BASE/Admin/admin/utilisateurs.php",              'page' => 'utilisateurs.php', 'title' => 'Utilisateurs'],
    ['icon' => 'gest',  'href' => "$BASE/Admin/admin/gestionnaires.php",             'page' => 'gestionnaires.php', 'title' => 'Gestionnaires (rôles)'],
    ['icon' => 'build', 'href' => "$BASE/Admin/admin/structures.php",                'page' => 'structures.php',   'title' => 'Structures'],
];
?>
</main>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');
body { padding-bottom: 120px; }
.gnav {
    position: fixed; left: 50%; bottom: 18px; transform: translateX(-50%);
    z-index: 9999; display: flex; align-items: center; gap: 6px;
    background: #650665; border-radius: 999px; padding: 8px 14px;
    box-shadow: 0 12px 34px rgba(40, 8, 40, .35);
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.gnav a { border: none; background: transparent; text-decoration: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; }
.gnav .gnav-item { width: 46px; height: 46px; border-radius: 50%;
    color: rgba(255,255,255,.72); transition: all .25s ease; }
.gnav .gnav-item svg { width: 22px; height: 22px; }
.gnav .gnav-item:hover { background: rgba(255,255,255,.12); color: #fff; }
.gnav .gnav-item.active { background: rgba(255,255,255,.18); color: #fff; }
.gnav .gnav-center { width: 54px; height: 54px; border-radius: 50%;
    background: #fff; color: #650665; transform: translateY(-10px);
    box-shadow: 0 10px 22px rgba(0,0,0,.30); transition: all .25s ease; }
.gnav .gnav-center svg { width: 26px; height: 26px; }
.gnav .gnav-center:hover { background: #f3e7f3; }
.gnav .gnav-sep { width: 1px; height: 26px; background: rgba(255,255,255,.25); margin: 0 4px; }
.gnav .gnav-avatar { position: relative; width: 46px; height: 46px; border-radius: 50%;
    background: #322336; color: #fff; font-weight: 800; font-size: 14px; }
.gnav .gnav-avatar:hover { filter: brightness(1.12); }
.gnav .gnav-dot { position: absolute; bottom: 2px; right: 2px; width: 9px; height: 9px;
    border-radius: 50%; background: #D4AF37; border: 2px solid #650665; }
</style>

<nav class="gnav" aria-label="Navigation administration">
    <?php foreach ($items as $it):
        $cls = !empty($it['center']) ? 'gnav-center' : 'gnav-item';
        if (empty($it['center']) && ($it['page'] ?? '') === $page) $cls .= ' active';
    ?>
    <a class="<?= $cls ?>" href="<?= htmlspecialchars($it['href'], ENT_QUOTES) ?>"
       title="<?= htmlspecialchars($it['title'], ENT_QUOTES) ?>"><?= $svg($it['icon']) ?></a>
    <?php endforeach; ?>

    <span class="gnav-sep" aria-hidden="true"></span>

    <a class="gnav-avatar" href="<?= $BASE ?>/profil.php" title="Mon profil">
        <span><?= htmlspecialchars($init) ?></span>
        <span class="gnav-dot" aria-hidden="true"></span>
    </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>