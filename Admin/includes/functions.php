<?php
/**
 * includes/functions.php
 * Fonctions utilitaires réutilisées dans tout le projet.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Échappe une chaîne pour un affichage HTML sûr (anti-XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL puis stoppe le script.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Indique si la requête courante est un envoi de formulaire (POST).
 */
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Récupère et nettoie un champ POST.
 */
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

/**
 * Enregistre un message flash (affiché une seule fois après une redirection).
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Récupère et supprime le message flash courant.
 */
function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Renvoie un temps relatif lisible ("il y a 5 min", "il y a 2 h"...).
 */
function temps_relatif(string $datetime): string
{
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    $diff = time() - $ts;
    if ($diff < 0) { $diff = 0; }

    if ($diff < 60)        { return "à l'instant"; }
    $min = intdiv($diff, 60);
    if ($min < 60)         { return "il y a {$min} min"; }
    $h = intdiv($min, 60);
    if ($h < 24)           { return "il y a {$h} h"; }
    $j = intdiv($h, 24);
    if ($j < 7)            { return "il y a {$j} j"; }
    return 'le ' . date('d/m/Y', $ts);
}
