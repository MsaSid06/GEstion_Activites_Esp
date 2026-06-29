<?php
/**
 * admin/gestionnaires.php
 * Gestion des gestionnaires : liste, attribution du rôle (promotion d'un
 * utilisateur existant), modification, retrait du rôle. Réservé au profil ADMIN.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../Gestionnaire/models/utilisateur.php';
require_once __DIR__ . '/../../Gestionnaire/models/appartenir.php';

// exiger_profil(['ADMIN']);

$user = utilisateur_courant();

// Niveaux d'accès (stockés dans UTILISATEUR.niveau_acces, entier).
$NIVEAUX = [1 => 'Utilisateur', 2 => 'Gestionnaire', 3 => 'Administrateur'];
$NIVCLS  = [1 => 'niv-dep',    2 => 'niv-fac', 3 => 'niv-glob'];

$mode   = 'liste';
$errors = [];
$old    = ['matricule' => '', 'prenom' => '', 'nom' => '', 'email' => '', 'id_struct' => '', 'niveau' => '1'];

/* ============================ Traitements POST ============================ */
if (is_post()) {
    $form_action = post('form_action');

    /* ---- Retrait du rôle gestionnaire ---- */
    if ($form_action === 'retirer') {
        $matricule = post('matricule');
        if ($matricule === $user['matricule']) {
            set_flash('erreur', 'Vous ne pouvez pas retirer votre propre rôle.');
        } else {
            try {
                $pdo->beginTransaction();

                // Plus de structure gérée (pas de modèle GESTIONNAIRE -> SQL direct).
                $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE matricule_user = :m')->execute([':m' => $matricule]);

                // Rétrograde le compte en PERSONNEL via le modèle (on conserve ses infos).
                $u  = getUtilisateurParMatricule($pdo, $matricule);
                $ok = $u && modifierUtilisateur(
                    $pdo,
                    $matricule,
                    $u['nom'],
                    $u['prenom'],
                    $u['email'],
                    $u['tel'] ?? null,
                    'PERSONNEL',
                    0
                );

                if ($ok) {
                    $pdo->commit();
                    set_flash('succes', 'Rôle de gestionnaire retiré.');
                } else {
                    $pdo->rollBack();
                    set_flash('erreur', 'Impossible de retirer le rôle pour cet utilisateur.');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                set_flash('erreur', 'Impossible de retirer le rôle pour cet utilisateur.');
            }
        }
        redirect('gestionnaires.php');
    }

    /* ---- Attribution / Modification ---- */
    if ($form_action === 'attribuer' || $form_action === 'modifier') {
        $old['matricule'] = strtoupper(post('matricule'));
        $old['prenom']    = post('prenom');
        $old['nom']       = post('nom');
        $old['email']     = post('email');
        $old['id_struct'] = post('id_struct');
        $old['niveau']    = post('niveau', '1');

        // L'utilisateur à promouvoir doit exister.
        $st = $pdo->prepare('SELECT matricule_user FROM UTILISATEUR WHERE matricule_user = :m');
        $st->execute([':m' => $old['matricule']]);
        if (!$st->fetchColumn()) {
            $errors['matricule'] = 'Aucun utilisateur avec ce matricule.';
        }

        if ($old['prenom'] === '') {
            $errors['prenom'] = 'Prénom requis.';
        }
        if ($old['nom'] === '') {
            $errors['nom'] = 'Nom requis.';
        }
        if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        }
        // Niveau choisi (1/2/3) -> profil correspondant.
        $niveau = (int) $old['niveau'];
        if (!isset($NIVEAUX[$niveau])) {
            $errors['niveau'] = "Niveau d'accès invalide.";
        }
        if ($niveau === 2) {
            $profilCible = 'GESTIONNAIRE';
        } elseif ($niveau === 3) {
            $profilCible = 'ADMIN';
        } else {
            // Utilisateur : on conserve le profil de base de la personne (Étudiant ou Personnel).
            $infoUser     = getUtilisateurParMatricule($pdo, $old['matricule']);
            $profilActuel = $infoUser['profil'] ?? 'PERSONNEL';
            $profilCible  = in_array($profilActuel, ['ETUDIANT', 'PERSONNEL'], true) ? $profilActuel : 'PERSONNEL';
        }
        // niveau_acces aligné sur le profil : étudiant 0, personnel 1, gestionnaire 2, admin 3.
        $NIVEAUX_PAR_PROFIL = ['ETUDIANT' => 0, 'PERSONNEL' => 1, 'GESTIONNAIRE' => 2, 'ADMIN' => 3];
        $niveauAcces = $NIVEAUX_PAR_PROFIL[$profilCible] ?? 1;

        $structOk = false;
        if ($old['id_struct'] !== '') {
            $s = $pdo->prepare('SELECT 1 FROM STRUCTURE WHERE id_struct = :s');
            $s->execute([':s' => $old['id_struct']]);
            $structOk = (bool) $s->fetchColumn();
        }
        if ($profilCible === 'GESTIONNAIRE' && !$structOk) {
            $errors['id_struct'] = 'Structure requise.';
        }

        // Email unique (en excluant le compte promu lui-même).
        if (!isset($errors['email']) && !isset($errors['matricule'])) {
            $s = $pdo->prepare('SELECT 1 FROM UTILISATEUR WHERE email = :e AND matricule_user <> :m');
            $s->execute([':e' => $old['email'], ':m' => $old['matricule']]);
            if ($s->fetchColumn()) {
                $errors['email'] = 'Cette adresse email est déjà utilisée.';
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Mise à jour du profil selon le niveau choisi (on conserve le téléphone existant).
                $courant = getUtilisateurParMatricule($pdo, $old['matricule']);
                $tel     = $courant['tel'] ?? null;
                $ok = modifierUtilisateur(
                    $pdo,
                    $old['matricule'],
                    $old['nom'],
                    $old['prenom'],
                    $old['email'],
                    $tel,
                    $profilCible,
                    $niveauAcces
                );

                if ($profilCible === 'GESTIONNAIRE') {
                    // Rattachement au département (table APPARTENIR) via le modèle.
                    if ($ok) {
                        $pdo->prepare('DELETE FROM APPARTENIR WHERE matricule_user = :m')->execute([':m' => $old['matricule']]);
                        $ok = creerAppartenance($pdo, $old['matricule'], $old['id_struct']) !== false;
                    }

                    // Structure gérée (pas de modèle GESTIONNAIRE -> SQL direct).
                    if ($ok) {
                        $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE matricule_user = :m')->execute([':m' => $old['matricule']]);
                        $ok = $pdo->prepare('INSERT INTO GESTIONNAIRE (matricule_user, id_struct) VALUES (:m, :s)')
                                  ->execute([':m' => $old['matricule'], ':s' => $old['id_struct']]);
                    }
                } else {
                    // Utilisateur / Administrateur : on retire les rattachements de gestionnaire.
                    if ($ok) {
                        $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE matricule_user = :m')->execute([':m' => $old['matricule']]);
                        $pdo->prepare('DELETE FROM APPARTENIR WHERE matricule_user = :m')->execute([':m' => $old['matricule']]);
                    }
                }

                if ($ok) {
                    $pdo->commit();
                    set_flash('succes', $form_action === 'attribuer' ? 'Rôle de gestionnaire attribué.' : 'Gestionnaire mis à jour.');
                    redirect('gestionnaires.php');
                } else {
                    $pdo->rollBack();
                    $errors['global'] = "Erreur lors de l'enregistrement.";
                    $mode = $form_action === 'attribuer' ? 'attribuer' : 'modifier';
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors['global'] = "Erreur lors de l'enregistrement.";
                $mode = $form_action === 'attribuer' ? 'attribuer' : 'modifier';
            }
        } else {
            $mode = $form_action === 'attribuer' ? 'attribuer' : 'modifier';
        }
    }
}

/* ============================ Affichage (GET) ============================ */
$action = $_GET['action'] ?? '';
if ($mode === 'liste' && $action === 'attribuer') {
    $mode = 'attribuer';
}
if ($mode === 'liste' && $action === 'modifier') {
    $matricule = $_GET['matricule'] ?? '';
    $stmt = $pdo->prepare('SELECT matricule_user, nom, prenom, email, niveau_acces FROM UTILISATEUR WHERE matricule_user = :m');
    $stmt->execute([':m' => $matricule]);
    $g = $stmt->fetch();
    if ($g) {
        $st = $pdo->prepare('SELECT id_struct FROM GESTIONNAIRE WHERE matricule_user = :m LIMIT 1');
        $st->execute([':m' => $matricule]);
        $old = [
            'matricule' => $g['matricule_user'],
            'prenom'    => $g['prenom'],
            'nom'       => $g['nom'],
            'email'     => $g['email'],
            'id_struct' => (string) ($st->fetchColumn() ?: ''),
            'niveau'    => (string) ((int) $g['niveau_acces'] ?: 1),
        ];
        $mode = 'modifier';
    } else {
        set_flash('erreur', 'Gestionnaire introuvable.');
        redirect('gestionnaires.php');
    }
}

$structures = $pdo->query('SELECT id_struct, nom_struct FROM STRUCTURE ORDER BY nom_struct')->fetchAll();

$gestionnaires = $pdo->query(
    "SELECT u.matricule_user, u.nom, u.prenom, u.email, u.niveau_acces,
            (SELECT s.nom_struct FROM GESTIONNAIRE g JOIN STRUCTURE s ON s.id_struct = g.id_struct
              WHERE g.matricule_user = u.matricule_user LIMIT 1) AS departement
     FROM UTILISATEUR u
     WHERE u.profil = 'GESTIONNAIRE'
     ORDER BY u.nom, u.prenom"
)->fetchAll();
$nb = count($gestionnaires);

$flash = get_flash();

$page_active = 'gestionnaires';
$titre       = 'Gestionnaires';
$head_auto   = false;

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="users-head">
    <div class="users-head-left">
        <a class="back-btn" href="dashboard.php"
            aria-label="Retour au tableau de bord"><?= icone('chevron-left', 20) ?></a>
        <span class="back-label">Tableau de bord</span>
        <div class="users-title">
            <h1>Gestionnaires</h1>
            <p><?= $nb ?>
                actif<?= $nb > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>
    <a class="btn-aubergine" href="gestionnaires.php?action=attribuer">
        <?= icone('user-check', 20) ?> Attribuer
        un rôle
    </a>
</div>

<?php if ($flash): ?>
<div
    class="alert alert-<?= $flash['type'] === 'erreur' ? 'error' : 'success' ?>">
    <?= e($flash['message']) ?></div>
<?php endif; ?>

<?php if ($mode === 'attribuer' || $mode === 'modifier'): ?>
<?php $estEdit = $mode === 'modifier'; ?>
<section class="panel-card form-card-admin">
    <h2 class="form-card-title">
        <?= $estEdit ? 'Modifier le gestionnaire' : 'Attribuer le rôle Gestionnaire' ?>
    </h2>
    <p class="form-card-sub">Renseignez les informations de l'utilisateur à promouvoir.</p>

    <?php if (!empty($errors['global'])): ?>
    <div class="alert alert-error">
        <?= e($errors['global']) ?></div>
    <?php endif; ?>

    <form method="post" action="gestionnaires.php" novalidate>
        <input type="hidden" name="form_action"
            value="<?= $estEdit ? 'modifier' : 'attribuer' ?>">

        <div class="grid-2">
            <div class="field">
                <label class="field-label" for="matricule">Matricule</label>
                <input
                    class="input <?= isset($errors['matricule']) ? 'input-error' : '' ?>"
                    type="text" id="matricule" name="matricule" maxlength="5"
                    value="<?= e($old['matricule']) ?>"
                    <?= $estEdit ? 'readonly' : '' ?>
                required>
                <?php if (isset($errors['matricule'])): ?><span
                    class="field-msg"><?= e($errors['matricule']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label class="field-label" for="prenom">Prénom</label>
                <input
                    class="input <?= isset($errors['prenom']) ? 'input-error' : '' ?>"
                    type="text" id="prenom" name="prenom"
                    value="<?= e($old['prenom']) ?>"
                    required>
                <?php if (isset($errors['prenom'])): ?><span
                    class="field-msg"><?= e($errors['prenom']) ?></span><?php endif; ?>
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label class="field-label" for="nom">Nom</label>
                <input
                    class="input <?= isset($errors['nom']) ? 'input-error' : '' ?>"
                    type="text" id="nom" name="nom"
                    value="<?= e($old['nom']) ?>"
                    required>
                <?php if (isset($errors['nom'])): ?><span
                    class="field-msg"><?= e($errors['nom']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input
                    class="input <?= isset($errors['email']) ? 'input-error' : '' ?>"
                    type="email" id="email" name="email"
                    value="<?= e($old['email']) ?>"
                    required>
                <?php if (isset($errors['email'])): ?><span
                    class="field-msg"><?= e($errors['email']) ?></span><?php endif; ?>
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label class="field-label" for="id_struct">Structure</label>
                <select
                    class="input select <?= isset($errors['id_struct']) ? 'input-error' : '' ?>"
                    id="id_struct" name="id_struct" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($structures as $s): ?>
                    <option
                        value="<?= e($s['id_struct']) ?>"
                        <?= $old['id_struct'] === $s['id_struct'] ? 'selected' : '' ?>>
                        <?= e($s['nom_struct']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['id_struct'])): ?><span
                    class="field-msg"><?= e($errors['id_struct']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <span class="field-label">Niveau d'accès</span>
                <div class="level-select">
                    <?php foreach ($NIVEAUX as $val => $lib): ?>
                    <input type="radio" name="niveau"
                        id="niv-<?= $val ?>"
                        value="<?= $val ?>"
                        <?= (int) $old['niveau'] === $val ? 'checked' : '' ?>>
                    <label
                        for="niv-<?= $val ?>"><?= $lib ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn-outline" href="gestionnaires.php">Annuler</a>
            <button type="submit" class="btn-aubergine">
                <?= icone('user-check', 18) ?>
                <?= $estEdit ? 'Enregistrer' : 'Attribuer' ?>
            </button>
        </div>
    </form>
</section>

<?php else: ?>

<section class="panel-card table-card">
    <div class="users-table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom Prénom</th>
                    <th>Email</th>
                    <th>Structure</th>
                    <th>Niveau d'accès</th>
                    <th class="ta-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gestionnaires)): ?>
                <tr>
                    <td colspan="6" class="recent-empty">Aucun gestionnaire pour le moment.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($gestionnaires as $g):
                    $niv = (int) $g['niveau_acces'];
                    $nivLib = $NIVEAUX[$niv] ?? 'Utilisateur';
                    $nivCls = $NIVCLS[$niv] ?? 'niv-dep'; ?>
                <tr>
                    <td class="td-mat">
                        <?= e($g['matricule_user']) ?>
                    </td>
                    <td class="td-nom">
                        <?= e($g['nom'] . ' ' . $g['prenom']) ?>
                    </td>
                    <td class="td-email">
                        <?= e($g['email']) ?>
                    </td>
                    <td class="td-dep">
                        <?= e($g['departement'] ?? '—') ?>
                    </td>
                    <td><span
                            class="niv niv-badge <?= $nivCls ?>"><?= e($nivLib) ?></span>
                    </td>
                    <td class="ta-right">
                        <div class="row-actions">
                            <a class="act-btn"
                                href="gestionnaires.php?action=modifier&matricule=<?= urlencode($g['matricule_user']) ?>"
                                aria-label="Modifier"><?= icone('edit', 18) ?></a>
                            <form method="post" action="gestionnaires.php" class="inline-form"
                                onsubmit="return confirm('Retirer le rôle de gestionnaire de <?= e($g['prenom'] . ' ' . $g['nom']) ?> ?');">
                                <input type="hidden" name="form_action" value="retirer">
                                <input type="hidden" name="matricule"
                                    value="<?= e($g['matricule_user']) ?>">
                                <button type="submit" class="act-btn act-danger"
                                    aria-label="Retirer le rôle"><?= icone('trash', 18) ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>