<?php
/**
 * auth/register.php
 * Création de compte (inscription libre). L'utilisateur déclare s'il est
 * Étudiant ou Personnel ; le rôle de gestion reste attribué ensuite par l'admin.
 *
 * Insertion en TRANSACTION dans UTILISATEUR + table fille (ETUDIANT ou PERSONNEL).
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (est_connecte()) {
    redirect('../index.php');
}

$erreurs = [];
$old = [
    'matricule' => '', 'nom' => '', 'prenom' => '', 'email' => '', 'tel' => '',
    'type' => '', 'filiere' => '', 'niveau' => '', 'poste' => '', 'specialite' => '',
];

if (is_post()) {
    // Récupération des champs.
    $old['matricule']  = strtoupper(post('matricule'));
    $old['nom']        = post('nom');
    $old['prenom']     = post('prenom');
    $old['email']      = post('email');
    $old['tel']        = post('tel');
    $old['type']       = post('type'); // 'etudiant' ou 'personnel'
    $old['filiere']    = post('filiere');
    $old['niveau']     = post('niveau');
    $old['poste']      = post('poste');
    $old['specialite'] = post('specialite');

    $motdepasse  = post('mot_de_passe');
    $confirmation = post('confirmation');

    // --- Validation des champs communs ---
    if ($old['matricule'] === '' || !preg_match('/^[A-Z0-9]{1,5}$/', $old['matricule'])) {
        $erreurs['matricule'] = 'Matricule requis (5 caractères maximum, lettres et chiffres).';
    }
    if ($old['nom'] === '')    { $erreurs['nom'] = 'Le nom est requis.'; }
    if ($old['prenom'] === '') { $erreurs['prenom'] = 'Le prénom est requis.'; }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Adresse email invalide.';
    }
    if ($motdepasse === '' || strlen($motdepasse) < 6) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($motdepasse !== $confirmation) {
        $erreurs['confirmation'] = 'Les deux mots de passe ne correspondent pas.';
    }

    // --- Validation du type et des champs spécifiques ---
    if ($old['type'] === 'etudiant') {
        if ($old['filiere'] === '') { $erreurs['filiere'] = 'La filière est requise.'; }
        if ($old['niveau'] === '')  { $erreurs['niveau'] = 'Le niveau est requis.'; }
    } elseif ($old['type'] === 'personnel') {
        if ($old['poste'] === '') { $erreurs['poste'] = 'Le poste est requis.'; }
    } else {
        $erreurs['type'] = 'Indiquez si vous êtes étudiant ou personnel.';
    }

    // --- Vérification des doublons (matricule, email, téléphone) ---
    if (empty($erreurs)) {
        $stmt = $pdo->prepare('SELECT matricule_user FROM UTILISATEUR WHERE matricule_user = :m');
        $stmt->execute([':m' => $old['matricule']]);
        if ($stmt->fetch()) {
            $erreurs['matricule'] = 'Ce matricule est déjà utilisé.';
        }

        $stmt = $pdo->prepare('SELECT matricule_user FROM UTILISATEUR WHERE email = :e');
        $stmt->execute([':e' => $old['email']]);
        if ($stmt->fetch()) {
            $erreurs['email'] = 'Cette adresse email est déjà enregistrée.';
        }

        if ($old['tel'] !== '') {
            $stmt = $pdo->prepare('SELECT matricule_user FROM UTILISATEUR WHERE tel = :t');
            $stmt->execute([':t' => $old['tel']]);
            if ($stmt->fetch()) {
                $erreurs['tel'] = 'Ce numéro de téléphone est déjà enregistré.';
            }
        }
    }

    // --- Insertion en transaction ---
    if (empty($erreurs)) {
        $profil = $old['type'] === 'etudiant' ? 'ETUDIANT' : 'PERSONNEL';
        $hash   = password_hash($motdepasse, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, niveau_acces)
                 VALUES (:m, :nom, :prenom, :email, :tel, :mdp, :profil, 0)'
            );
            $stmt->execute([
                ':m'      => $old['matricule'],
                ':nom'    => $old['nom'],
                ':prenom' => $old['prenom'],
                ':email'  => $old['email'],
                ':tel'    => $old['tel'] !== '' ? $old['tel'] : null,
                ':mdp'    => $hash,
                ':profil' => $profil,
            ]);

            if ($profil === 'ETUDIANT') {
                $stmt = $pdo->prepare(
                    'INSERT INTO ETUDIANT (matricule_user, filiere, niveau) VALUES (:m, :filiere, :niveau)'
                );
                $stmt->execute([
                    ':m'       => $old['matricule'],
                    ':filiere' => $old['filiere'],
                    ':niveau'  => $old['niveau'],
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO PERSONNEL (matricule_user, poste, specialite) VALUES (:m, :poste, :specialite)'
                );
                $stmt->execute([
                    ':m'          => $old['matricule'],
                    ':poste'      => $old['poste'],
                    ':specialite' => $old['specialite'] !== '' ? $old['specialite'] : null,
                ]);
            }

            $pdo->commit();
            set_flash('succes', 'Compte créé avec succès. Vous pouvez maintenant vous connecter.');
            redirect('login.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreurs['global'] = 'Une erreur est survenue lors de la création du compte. Réessayez.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte — ESP Dakar</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-split">

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

    <main class="form-panel">
        <div class="form-card form-card-wide">
            <h2 class="form-title">Créer un compte</h2>
            <p class="form-sub">Rejoignez la plateforme de planification des activités</p>

            <?php if (!empty($erreurs['global'])): ?>
                <div class="alert alert-error"><?= e($erreurs['global']) ?></div>
            <?php endif; ?>

            <form method="post" action="register.php" id="register-form" novalidate>

                <div class="grid-2">
                    <div class="field">
                        <label class="field-label" for="matricule">Matricule</label>
                        <input class="input <?= isset($erreurs['matricule']) ? 'input-error' : '' ?>"
                               type="text" name="matricule" id="matricule" maxlength="5"
                               placeholder="ex : U013" value="<?= e($old['matricule']) ?>" required>
                        <?php if (isset($erreurs['matricule'])): ?>
                            <span class="field-msg"><?= e($erreurs['matricule']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field-label" for="tel">Téléphone</label>
                        <input class="input <?= isset($erreurs['tel']) ? 'input-error' : '' ?>"
                               type="tel" name="tel" id="tel"
                               placeholder="77 000 00 00" value="<?= e($old['tel']) ?>">
                        <?php if (isset($erreurs['tel'])): ?>
                            <span class="field-msg"><?= e($erreurs['tel']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label class="field-label" for="prenom">Prénom</label>
                        <input class="input <?= isset($erreurs['prenom']) ? 'input-error' : '' ?>"
                               type="text" name="prenom" id="prenom"
                               placeholder="Votre prénom" value="<?= e($old['prenom']) ?>" required>
                        <?php if (isset($erreurs['prenom'])): ?>
                            <span class="field-msg"><?= e($erreurs['prenom']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field-label" for="nom">Nom</label>
                        <input class="input <?= isset($erreurs['nom']) ? 'input-error' : '' ?>"
                               type="text" name="nom" id="nom"
                               placeholder="Votre nom" value="<?= e($old['nom']) ?>" required>
                        <?php if (isset($erreurs['nom'])): ?>
                            <span class="field-msg"><?= e($erreurs['nom']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="email">Adresse email</label>
                    <input class="input <?= isset($erreurs['email']) ? 'input-error' : '' ?>"
                           type="email" name="email" id="email"
                           placeholder="votre.email@esp.sn" value="<?= e($old['email']) ?>" required>
                    <?php if (isset($erreurs['email'])): ?>
                        <span class="field-msg"><?= e($erreurs['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label class="field-label" for="mot_de_passe">Mot de passe</label>
                        <input class="input <?= isset($erreurs['mot_de_passe']) ? 'input-error' : '' ?>"
                               type="password" name="mot_de_passe" id="mot_de_passe"
                               placeholder="••••••••" required>
                        <?php if (isset($erreurs['mot_de_passe'])): ?>
                            <span class="field-msg"><?= e($erreurs['mot_de_passe']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field-label" for="confirmation">Confirmer le mot de passe</label>
                        <input class="input <?= isset($erreurs['confirmation']) ? 'input-error' : '' ?>"
                               type="password" name="confirmation" id="confirmation"
                               placeholder="••••••••" required>
                        <?php if (isset($erreurs['confirmation'])): ?>
                            <span class="field-msg"><?= e($erreurs['confirmation']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Choix du statut : boutons radio -->
                <span class="field-label small-caps">Vous êtes</span>
                <div class="type-select">
                    <input type="radio" name="type" id="type-etd" value="etudiant"
                           <?= $old['type'] === 'etudiant' ? 'checked' : '' ?>>
                    <label for="type-etd">Étudiant</label>

                    <input type="radio" name="type" id="type-pers" value="personnel"
                           <?= $old['type'] === 'personnel' ? 'checked' : '' ?>>
                    <label for="type-pers">Personnel</label>
                </div>
                <?php if (isset($erreurs['type'])): ?>
                    <span class="field-msg"><?= e($erreurs['type']) ?></span>
                <?php endif; ?>

                <!-- Champs spécifiques ÉTUDIANT (affichés après le choix) -->
                <div class="conditional" id="bloc-etudiant" data-type="etudiant" hidden>
                    <div class="grid-2">
                        <div class="field">
                            <label class="field-label" for="filiere">Filière</label>
                            <input class="input <?= isset($erreurs['filiere']) ? 'input-error' : '' ?>"
                                   type="text" name="filiere" id="filiere"
                                   placeholder="ex : Génie Logiciel" value="<?= e($old['filiere']) ?>">
                            <?php if (isset($erreurs['filiere'])): ?>
                                <span class="field-msg"><?= e($erreurs['filiere']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="field">
                            <label class="field-label" for="niveau">Niveau</label>
                            <input class="input <?= isset($erreurs['niveau']) ? 'input-error' : '' ?>"
                                   type="text" name="niveau" id="niveau"
                                   placeholder="ex : Licence 2" value="<?= e($old['niveau']) ?>">
                            <?php if (isset($erreurs['niveau'])): ?>
                                <span class="field-msg"><?= e($erreurs['niveau']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Champs spécifiques PERSONNEL (affichés après le choix) -->
                <div class="conditional" id="bloc-personnel" data-type="personnel" hidden>
                    <div class="grid-2">
                        <div class="field">
                            <label class="field-label" for="poste">Poste</label>
                            <input class="input <?= isset($erreurs['poste']) ? 'input-error' : '' ?>"
                                   type="text" name="poste" id="poste"
                                   placeholder="ex : Secrétaire" value="<?= e($old['poste']) ?>">
                            <?php if (isset($erreurs['poste'])): ?>
                                <span class="field-msg"><?= e($erreurs['poste']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="field">
                            <label class="field-label" for="specialite">Spécialité <span class="opt">(optionnel)</span></label>
                            <input class="input" type="text" name="specialite" id="specialite"
                                   placeholder="ex : Réseaux et systèmes" value="<?= e($old['specialite']) ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-gold">Créer mon compte</button>

                <p class="form-foot">
                    Déjà inscrit ?
                    <a href="login.php">Se connecter</a>
                </p>
            </form>
        </div>
    </main>

</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
