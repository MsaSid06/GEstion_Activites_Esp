<?php
/**
 * auth/login.php
 * Page de connexion (design conforme à la maquette) + traitement du formulaire.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Déjà connecté ? On va directement au tableau de bord.
if (est_connecte()) {
    redirect('../index.php');
}

$erreur = null;
$email_saisi = '';
$espace_saisi = 'etudiant_personnel';

if (is_post()) {
    $email_saisi  = post('email');
    $motdepasse   = post('mot_de_passe');
    $espace_saisi = post('espace', 'etudiant_personnel');

    $profils = profils_autorises($espace_saisi);

    if ($email_saisi === '' || $motdepasse === '') {
        $erreur = 'Renseignez votre email et votre mot de passe.';
    } elseif (empty($profils)) {
        $erreur = 'Profil d\'accès invalide.';
    } else {
        // On récupère l'utilisateur par email.
        $stmt = $pdo->prepare('SELECT matricule_user, nom, prenom, email, mot_de_passe, profil
                               FROM UTILISATEUR WHERE email = :email');
        $stmt->execute([':email' => $email_saisi]);
        $user = $stmt->fetch();

        // Vérification mot de passe + cohérence avec l'espace d'accès choisi.
        if (!$user || !password_verify($motdepasse, $user['mot_de_passe'])) {
            $erreur = 'Email ou mot de passe incorrect.';
        } elseif (!in_array($user['profil'], $profils, true)) {
            $erreur = 'Ce compte n\'est pas autorisé pour le profil d\'accès sélectionné.';
        } else {
            connecter_utilisateur($user);
            // Aiguillage selon le profil.
            redirect($user['profil'] === 'ADMIN' ? '../admin/dashboard.php' : '../index.php');
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ESP Dakar</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-split">

    <!-- Panneau gauche : identité visuelle -->
    <aside class="brand-panel">
        <div class="brand-rings" aria-hidden="true"></div>
        <div class="brand-top">
            <div class="brand-logo">ESP</div>
            <h1 class="brand-title">École Supérieure<br>Polytechnique<br>de Dakar</h1>
            <p class="brand-sub">Plateforme de planification et de gestion des activités annuelles de l'ESP.</p>
        </div>
        <div class="brand-stats">
            <div class="stat">
                <span class="stat-num">48</span>
                <span class="stat-label">Activités</span>
            </div>
            <div class="stat">
                <span class="stat-num">6</span>
                <span class="stat-label">Départements</span>
            </div>
            <div class="stat">
                <span class="stat-num">1 200+</span>
                <span class="stat-label">Utilisateurs</span>
            </div>
        </div>
    </aside>

    <!-- Panneau droit : formulaire -->
    <main class="form-panel">
        <div class="form-card">
            <h2 class="form-title">Connexion</h2>
            <p class="form-sub">Planification des activités — Accès sécurisé</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) === 'erreur' ? 'error' : 'success' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($erreur): ?>
                <div class="alert alert-error"><?= e($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>
                <span class="field-label small-caps">Profil d'accès</span>
                <div class="profile-select">
                    <input type="radio" name="espace" id="esp-ep" value="etudiant_personnel"
                           <?= $espace_saisi === 'etudiant_personnel' ? 'checked' : '' ?>>
                    <label for="esp-ep">Étudiant /<br>Personnel</label>

                    <input type="radio" name="espace" id="esp-gest" value="gestionnaire"
                           <?= $espace_saisi === 'gestionnaire' ? 'checked' : '' ?>>
                    <label for="esp-gest">Gestionnaire</label>

                    <input type="radio" name="espace" id="esp-admin" value="admin"
                           <?= $espace_saisi === 'admin' ? 'checked' : '' ?>>
                    <label for="esp-admin">Administrateur</label>
                </div>

                <div class="field">
                    <label class="field-label" for="email">Adresse email</label>
                    <input class="input" type="email" name="email" id="email"
                           placeholder="votre.email@esp.sn" value="<?= e($email_saisi) ?>" required>
                </div>

                <div class="field">
                    <label class="field-label" for="mot_de_passe">Mot de passe</label>
                    <input class="input" type="password" name="mot_de_passe" id="mot_de_passe"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-gold">Se connecter</button>

                <p class="form-foot">
                    Pas encore de compte ?
                    <a href="register.php">Créer un compte</a>
                </p>
            </form>
        </div>
    </main>

</div>
</body>
</html>
