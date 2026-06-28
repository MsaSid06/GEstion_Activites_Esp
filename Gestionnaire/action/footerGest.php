<?php
/**
 * Gestionnaire/action/footerGest.php
 * Barre de navigation flottante du Gestionnaire (autonome).
 * Style aubergine : pastille pleine #650665, icônes claires, bouton « + »
 * blanc pour la création, avatar sombre avec pastille dorée.
 * N'a besoin ni de Tailwind ni de Font Awesome (CSS + icônes SVG intégrés).
 */
if (session_status() === PHP_SESSION_NONE && !headers_sent()) session_start();

$BASE   = '/GestionDesActiviteEsp';
$page   = basename(parse_url($_SERVER['PHP_SELF'] ?? '', PHP_URL_PATH));
$prenom = $_SESSION['prenom'] ?? '';
$init   = $prenom !== '' ? mb_strtoupper(mb_substr($prenom, 0, 1)) : '';

// Icônes SVG (Heroicons outline)
$svg = function (string $n): string {
    $p = [
        'home' => '<path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>',
        'cal'  => '<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25"/>',
        'plus' => '<path d="M12 4.5v15m7.5-7.5h-15"/>',
        'bell' => '<path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
    ];
    $sw = $n === 'plus' ? '2.2' : '1.8';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $sw
         . '" stroke-linecap="round" stroke-linejoin="round">' . ($p[$n] ?? '') . '</svg>';
};

$items = [
    ['icon' => 'home', 'href' => "$BASE/Gestionnaire/DashboardGestionnaire.php", 'page' => 'DashboardGestionnaire.php', 'title' => 'Accueil'],
    ['icon' => 'cal',  'href' => "$BASE/Gestionnaire/action/mesActivites.php",     'page' => 'mesActivites.php',          'title' => 'Mes activités'],
    ['icon' => 'plus', 'href' => "$BASE/Gestionnaire/action/formCreationActivite.php", 'page' => 'formCreationActivite.php', 'title' => 'Créer une activité', 'center' => true],
    ['icon' => 'bell', 'href' => "$BASE/Gestionnaire/action/Affiche_notif.php",    'page' => 'Affiche_notif.php',         'title' => 'Notifications'],
];
?>
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

<nav class="gnav" aria-label="Navigation Gestionnaire">
    <?php foreach ($items as $it):
        $cls = !empty($it['center']) ? 'gnav-center' : 'gnav-item';
        if (empty($it['center']) && $it['page'] === $page) $cls .= ' active';
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