<?php
/**
 * includes/icons.php
 * Petites icônes SVG outline (trait = couleur héritée via currentColor).
 * Évite toute dépendance externe et reste fidèle au style de la maquette.
 */

function icone(string $nom, int $taille = 24): string
{
    $s = $taille;
    $base = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" ' . $base . ' aria-hidden="true">';

    switch ($nom) {
        case 'users':
            $p = '<path d="M9 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6"/><path d="M3 20v-1a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v1"/><path d="M16 3.1a3 3 0 0 1 0 5.8"/><path d="M21 20v-1a4 4 0 0 0-3-3.8"/>';
            break;
        case 'calendar':
            $p = '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M16 3v4M8 3v4M4 11h16"/>';
            break;
        case 'user-check':
            $p = '<path d="M9 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6"/><path d="M3 20v-1a4 4 0 0 1 4-4h4a4 4 0 0 1 3 1.4"/><path d="M16 16l2 2 4-4"/>';
            break;
        case 'building':
            $p = '<rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/><path d="M5 21h14"/>';
            break;
        case 'bell':
            $p = '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>';
            break;
        case 'logout':
            $p = '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>';
            break;
        case 'grid':
            $p = '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>';
            break;
        case 'chevron-right':
            $p = '<path d="M9 6l6 6-6 6"/>';
            break;
        case 'chart-bar':
            $p = '<path d="M4 20V10M10 20V4M16 20v-6M22 20H2"/>';
            break;
        case 'edit':
            $p = '<path d="M4 20h4l10-10a2 2 0 0 0-3-3L5 17v3z"/><path d="M13.5 6.5l3 3"/>';
            break;
        case 'trash':
            $p = '<path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><path d="M9 7V4h6v3"/>';
            break;
        case 'plus':
            $p = '<path d="M12 5v14M5 12h14"/>';
            break;
        case 'chevron-left':
            $p = '<path d="M15 6l-6 6 6 6"/>';
            break;
        case 'x':
            $p = '<path d="M6 6l12 12M18 6L6 18"/>';
            break;
        case 'check':
            $p = '<path d="M5 12l5 5L20 7"/>';
            break;
        case 'check-circle':
            $p = '<circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>';
            break;
        case 'alert-circle':
            $p = '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>';
            break;
        default:
            $p = '';
    }

    return $svg . $p . '</svg>';
}
