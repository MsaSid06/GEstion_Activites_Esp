<?php
/**
 * includes/auth.php
 * Gestion de la session utilisateur et des contrôles d'accès par rôle.
 *
 * Le champ "profil" en base vaut : ADMIN, GESTIONNAIRE, ETUDIANT ou PERSONNEL.
 * Sur la page de connexion, l'utilisateur choisit un "espace d'accès" :
 *   - etudiant_personnel  -> profils ETUDIANT ou PERSONNEL
 *   - gestionnaire        -> profil  GESTIONNAIRE
 *   - admin               -> profil  ADMIN
 */

require_once './functions.php';

/**
 * Correspondance entre l'espace d'accès choisi à la connexion
 * et les profils réellement autorisés à y entrer.
 */
function profils_autorises(string $espace): array
{
    switch ($espace) {
        case 'etudiant_personnel':
            return ['ETUDIANT', 'PERSONNEL'];
        case 'gestionnaire':
            return ['GESTIONNAIRE'];
        case 'admin':
            return ['ADMIN'];
        default:
            return [];
    }
}

/**
 * Ouvre la session applicative après une connexion réussie.
 */
function connecter_utilisateur(array $user): void
{
    // Régénère l'identifiant de session pour éviter la fixation de session.
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'matricule' => $user['matricule_user'],
        'nom'       => $user['nom'],
        'prenom'    => $user['prenom'],
        'email'     => $user['email'],
        'profil'    => $user['profil'],
    ];
}

/**
 * Indique si un utilisateur est connecté.
 */
function est_connecte(): bool
{
    return !empty($_SESSION['user']);
}

/**
 * Retourne l'utilisateur connecté (ou null).
 */
function utilisateur_courant(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Exige une session active, sinon renvoie vers la connexion.
 */
function exiger_connexion(): void
{
    if (!est_connecte()) {
        set_flash('erreur', 'Vous devez vous connecter pour accéder à cette page.');
        redirect('auth/login.php');
    }
}

/**
 * Exige un profil précis (ou l'un des profils listés).
 */
// function exiger_profil(array $profils): void
// {
//     exiger_connexion();
//     if (!in_array($_SESSION['user']['profil'], $profils, true)) {
//         http_response_code(403);
//         exit('Accès refusé : vous n\'avez pas les droits nécessaires pour cette page.');
//     }
// }

/**
 * Ferme la session applicative.
 */
function deconnecter_utilisateur(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
