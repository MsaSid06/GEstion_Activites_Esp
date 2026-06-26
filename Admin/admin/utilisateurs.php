<?php
/**
 * admin/utilisateurs.php
 * Gestion des comptes : liste, création, modification, suppression.
 * Réservé au profil ADMIN.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// exiger_profil(['ADMIN']);

$user  = utilisateur_courant();
$ROLES = [
    'ETUDIANT'     => 'Étudiant',
    'PERSONNEL'    => 'Personnel',
    'GESTIONNAIRE' => 'Gestionnaire',
    'ADMIN'        => 'Admin',
];

/**
 * Génère le prochain matricule libre au format U001, U002... (colonne char(5)).
 */
function generer_matricule(PDO $pdo): string
{
    $dernier = $pdo->query(
        "SELECT matricule_user FROM UTILISATEUR
         WHERE matricule_user REGEXP '^U[0-9]+$'
         ORDER BY CAST(SUBSTRING(matricule_user, 2) AS UNSIGNED) DESC LIMIT 1"
    )->fetchColumn();

    $n = $dernier ? ((int) substr($dernier, 1)) + 1 : 1;
    return 'U' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
}

$mode   = 'liste';
$errors = [];
$old    = ['matricule' => '', 'prenom' => '', 'nom' => '', 'email' => '', 'id_struct' => '', 'role' => 'ETUDIANT'];

/* ============================ Traitements POST ============================ */
if (is_post()) {
    $form_action = post('form_action');

    /* ---- Suppression ---- */
    if ($form_action === 'supprimer') {
        $matricule = post('matricule');
        if ($matricule === $user['matricule']) {
            set_flash('erreur', 'Vous ne pouvez pas supprimer votre propre compte.');
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM UTILISATEUR WHERE matricule_user = :m');
                $stmt->execute([':m' => $matricule]);
                set_flash('succes', 'Compte supprimé.');
            } catch (PDOException $e) {
                set_flash('erreur', 'Suppression impossible : cet utilisateur a des activités ou notifications associées.');
            }
        }
        redirect('utilisateurs.php');
    }

    /* ---- Création / Modification ---- */
    if ($form_action === 'creer' || $form_action === 'modifier') {
        $old['matricule'] = post('matricule');
        $old['prenom']    = post('prenom');
        $old['nom']       = post('nom');
        $old['email']     = post('email');
        $old['id_struct'] = post('id_struct');
        $old['role']      = post('role');

        if ($old['prenom'] === '') {
            $errors['prenom'] = 'Prénom requis.';
        }
        if ($old['nom'] === '') {
            $errors['nom'] = 'Nom requis.';
        }
        if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        }
        if (!isset($ROLES[$old['role']])) {
            $errors['role'] = 'Rôle invalide.';
        }

        // La structure choisie doit exister.
        $structOk = false;
        if ($old['id_struct'] !== '') {
            $st = $pdo->prepare('SELECT 1 FROM STRUCTURE WHERE id_struct = :s');
            $st->execute([':s' => $old['id_struct']]);
            $structOk = (bool) $st->fetchColumn();
        }
        if (!$structOk) {
            $errors['id_struct'] = 'Structure requise.';
        }

        // Unicité de l'email (en excluant le compte courant lors d'une modification).
        if (!isset($errors['email'])) {
            $sql = 'SELECT 1 FROM UTILISATEUR WHERE email = :e';
            $par = [':e' => $old['email']];
            if ($form_action === 'modifier') {
                $sql .= ' AND matricule_user <> :m';
                $par[':m'] = $old['matricule'];
            }
            $st = $pdo->prepare($sql);
            $st->execute($par);
            if ($st->fetchColumn()) {
                $errors['email'] = 'Cette adresse email est déjà utilisée.';
            }
        }

        if (empty($errors)) {
            $niveau = $old['role'] === 'ADMIN' ? 9 : ($old['role'] === 'GESTIONNAIRE' ? 1 : 0);

            try {
                $pdo->beginTransaction();

                if ($form_action === 'creer') {
                    $matricule = generer_matricule($pdo);
                    $tmp       = 'Esp' . random_int(1000, 9999);
                    $hash      = password_hash($tmp, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare(
                        'INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, niveau_acces)
                         VALUES (:m, :nom, :prenom, :email, NULL, :mdp, :profil, :niv)'
                    );
                    $stmt->execute([
                        ':m' => $matricule, ':nom' => $old['nom'], ':prenom' => $old['prenom'],
                        ':email' => $old['email'], ':mdp' => $hash, ':profil' => $old['role'], ':niv' => $niveau,
                    ]);
                } else {
                    $matricule = $old['matricule'];
                    $stmt = $pdo->prepare(
                        'UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email,
                         profil = :profil, niveau_acces = :niv WHERE matricule_user = :m'
                    );
                    $stmt->execute([
                        ':nom' => $old['nom'], ':prenom' => $old['prenom'], ':email' => $old['email'],
                        ':profil' => $old['role'], ':niv' => $niveau, ':m' => $matricule,
                    ]);
                }

                // Rattachement à un département (table APPARTENIR) : une seule appartenance.
                $pdo->prepare('DELETE FROM APPARTENIR WHERE matricule_user = :m')->execute([':m' => $matricule]);
                $pdo->prepare('INSERT INTO APPARTENIR (id_struct, matricule_user) VALUES (:s, :m)')
                    ->execute([':s' => $old['id_struct'], ':m' => $matricule]);

                // Rôle gestionnaire : (re)synchronise la table GESTIONNAIRE.
                $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE matricule_user = :m')->execute([':m' => $matricule]);
                if ($old['role'] === 'GESTIONNAIRE') {
                    $pdo->prepare('INSERT INTO GESTIONNAIRE (matricule_user, id_struct) VALUES (:m, :s)')
                        ->execute([':m' => $matricule, ':s' => $old['id_struct']]);
                }

                // Rôle administrateur : (re)synchronise la table ADMINISTRATEUR.
                $pdo->prepare('DELETE FROM ADMINISTRATEUR WHERE matricule_user = :m')->execute([':m' => $matricule]);
                if ($old['role'] === 'ADMIN') {
                    $pdo->prepare('INSERT INTO ADMINISTRATEUR (matricule_user) VALUES (:m)')->execute([':m' => $matricule]);
                }

                $pdo->commit();

                if ($form_action === 'creer') {
                    set_flash('succes', "Compte créé — matricule : {$matricule} · mot de passe provisoire : {$tmp} (à communiquer à l'utilisateur).");
                } else {
                    set_flash('succes', 'Compte mis à jour.');
                }
                redirect('utilisateurs.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors['global'] = "Erreur lors de l'enregistrement. Vérifie que la base est à jour (table APPARTENIR).";
                $mode = $form_action === 'creer' ? 'nouveau' : 'modifier';
            }
        } else {
            $mode = $form_action === 'creer' ? 'nouveau' : 'modifier';
        }
    }
}

/* ============================ Affichage (GET) ============================ */
$action = $_GET['action'] ?? '';
if ($mode === 'liste' && $action === 'nouveau') {
    $mode = 'nouveau';
}
if ($mode === 'liste' && $action === 'modifier') {
    $matricule = $_GET['matricule'] ?? '';
    $stmt = $pdo->prepare('SELECT matricule_user, nom, prenom, email, profil FROM UTILISATEUR WHERE matricule_user = :m');
    $stmt->execute([':m' => $matricule]);
    $u = $stmt->fetch();
    if ($u) {
        $struct = $pdo->prepare('SELECT id_struct FROM APPARTENIR WHERE matricule_user = :m LIMIT 1');
        $struct->execute([':m' => $matricule]);
        $old = [
            'matricule' => $u['matricule_user'],
            'prenom'    => $u['prenom'],
            'nom'       => $u['nom'],
            'email'     => $u['email'],
            'id_struct' => (string) ($struct->fetchColumn() ?: ''),
            'role'      => $u['profil'],
        ];
        $mode = 'modifier';
    } else {
        set_flash('erreur', 'Utilisateur introuvable.');
        redirect('utilisateurs.php');
    }
}

// Liste des structures (pour les listes déroulantes "Structure").
$structures = $pdo->query('SELECT id_struct, nom_struct FROM STRUCTURE ORDER BY nom_struct')->fetchAll();

// Liste des comptes + département de chacun.
$comptes = $pdo->query(
    "SELECT u.matricule_user, u.nom, u.prenom, u.email, u.profil,
            (SELECT s.nom_struct FROM APPARTENIR ap JOIN STRUCTURE s ON s.id_struct = ap.id_struct
              WHERE ap.matricule_user = u.matricule_user LIMIT 1) AS departement
     FROM UTILISATEUR u
     ORDER BY u.nom, u.prenom"
)->fetchAll();
$nb_comptes = count($comptes);

$flash = get_flash();

$page_active = 'utilisateurs';
$titre       = 'Utilisateurs';
$head_auto   = false; // on gère l'en-tête de page nous-mêmes

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="users-head">
    <div class="users-head-left">
        <a class="back-btn" href="dashboard.php" aria-label="Retour au tableau de bord">
            <?= icone('chevron-left', 20) ?>
        </a>
        <span class="back-label">Tableau de bord</span>
        <div class="users-title">
            <h1>Utilisateurs</h1>
            <p><?= $nb_comptes ?>
                compte<?= $nb_comptes > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>
    <a class="btn-aubergine" href="utilisateurs.php?action=nouveau">
        <?= icone('plus', 20) ?> Créer un compte
    </a>
</div>

<?php if ($flash): ?>
<div
    class="alert alert-<?= $flash['type'] === 'erreur' ? 'error' : 'success' ?>">
    <?= e($flash['message']) ?></div>
<?php endif; ?>

<?php if ($mode === 'nouveau' || $mode === 'modifier'): ?>
<?php
    $estEdit   = $mode === 'modifier';
    $formTitle = $estEdit ? 'Modifier le compte' : 'Nouveau compte';
    ?>
<section class="panel-card form-card-admin">
    <h2 class="form-card-title"><?= e($formTitle) ?></h2>

    <?php if (!empty($errors['global'])): ?>
    <div class="alert alert-error">
        <?= e($errors['global']) ?></div>
    <?php endif; ?>

    <form method="post" action="utilisateurs.php" novalidate>
        <input type="hidden" name="form_action"
            value="<?= $estEdit ? 'modifier' : 'creer' ?>">
        <?php if ($estEdit): ?>
        <input type="hidden" name="matricule"
            value="<?= e($old['matricule']) ?>">
        <?php endif; ?>

        <div class="grid-2">
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
        </div>

        <div class="grid-2">
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
            <div class="field">
                <label class="field-label" for="id_struct">Structure</label>
                <select
                    class="input select <?= isset($errors['id_struct']) ? 'input-error' : '' ?>"
                    id="id_struct" name="id_struct" required>
                    <option value="">— Choisir —</option>
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
        </div>

        <div class="field field-half">
            <label class="field-label" for="role">Rôle</label>
            <select class="input select" id="role" name="role">
                <?php foreach ($ROLES as $val => $lib): ?>
                <option value="<?= $val ?>" <?= $old['role'] === $val ? 'selected' : '' ?>><?= $lib ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <a class="btn-outline" href="utilisateurs.php">Annuler</a>
            <button type="submit"
                class="btn-aubergine"><?= $estEdit ? 'Enregistrer' : 'Créer' ?></button>
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
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Structure</th>
                    <th class="ta-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comptes)): ?>
                <tr>
                    <td colspan="7" class="recent-empty">Aucun compte enregistré.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($comptes as $c):
                    $roleLib = $ROLES[$c['profil']] ?? $c['profil'];
                    $roleCls = strtolower($c['profil']); ?>
                <tr>
                    <td class="td-mat">
                        <?= e($c['matricule_user']) ?>
                    </td>
                    <td class="td-nom">
                        <?= e($c['nom']) ?></td>
                    <td><?= e($c['prenom']) ?>
                    </td>
                    <td class="td-email">
                        <?= e($c['email']) ?>
                    </td>
                    <td><span
                            class="role role-<?= $roleCls ?>"><?= e($roleLib) ?></span>
                    </td>
                    <td class="td-dep">
                        <?= e($c['departement'] ?? '—') ?>
                    </td>
                    <td class="ta-right">
                        <div class="row-actions">
                            <a class="act-btn"
                                href="utilisateurs.php?action=modifier&matricule=<?= urlencode($c['matricule_user']) ?>"
                                aria-label="Modifier"><?= icone('edit', 18) ?></a>
                            <form method="post" action="utilisateurs.php" class="inline-form"
                                onsubmit="return confirm('Supprimer le compte de <?= e($c['prenom'] . ' ' . $c['nom']) ?> ?');">
                                <input type="hidden" name="form_action" value="supprimer">
                                <input type="hidden" name="matricule"
                                    value="<?= e($c['matricule_user']) ?>">
                                <button type="submit" class="act-btn act-danger"
                                    aria-label="Supprimer"><?= icone('trash', 18) ?></button>
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