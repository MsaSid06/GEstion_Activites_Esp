<?php
/**
 * admin/structures.php
 * Gestion de toutes les structures (DEPARTEMENT, SERVICE, AMICALE, ASSOCIATION) :
 * liste en cartes, ajout, modification, suppression. Réservé au profil ADMIN.
 *
 * Notes de modélisation :
 *  - "Responsable" = gestionnaire rattaché à la structure (table GESTIONNAIRE).
 *  - mail et tel sont NOT NULL UNIQUE mais absents du formulaire : ils sont
 *    générés automatiquement (placeholders) à la création.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exiger_profil(['ADMIN']);

$user = utilisateur_courant();

$TYPES = [
    'DEPARTEMENT' => 'Département',
    'SERVICE'     => 'Service',
    'AMICALE'     => 'Amicale',
    'ASSOCIATION' => 'Association',
];
$TYPECLS = [
    'DEPARTEMENT' => 'type-dep',
    'SERVICE'     => 'type-serv',
    'AMICALE'     => 'type-ami',
    'ASSOCIATION' => 'type-asso',
];

/** Prochain identifiant de structure libre (S001, S002...). */
function generer_id_struct(PDO $pdo): string
{
    $last = $pdo->query(
        "SELECT id_struct FROM STRUCTURE WHERE id_struct REGEXP '^S[0-9]+$'
         ORDER BY CAST(SUBSTRING(id_struct, 2) AS UNSIGNED) DESC LIMIT 1"
    )->fetchColumn();
    $n = $last ? ((int) substr($last, 1)) + 1 : 1;
    return 'S' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
}

$mode   = 'liste';
$errors = [];
$old    = ['id_struct' => '', 'nom' => '', 'type' => 'DEPARTEMENT', 'responsable' => ''];

/* ============================ Traitements POST ============================ */
if (is_post()) {
    $form_action = post('form_action');

    /* ---- Suppression ---- */
    if ($form_action === 'supprimer') {
        $id = post('id_struct');
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE id_struct = :s')->execute([':s' => $id]);
            $pdo->prepare('DELETE FROM STRUCTURE WHERE id_struct = :s')->execute([':s' => $id]);
            $pdo->commit();
            set_flash('succes', 'Structure supprimée.');
        } catch (PDOException $e) {
            $pdo->rollBack();
            set_flash('erreur', 'Suppression impossible (éléments liés à cette structure).');
        }
        redirect('structures.php');
    }

    /* ---- Ajout / Modification ---- */
    if ($form_action === 'creer' || $form_action === 'modifier') {
        $old['id_struct']   = post('id_struct');
        $old['nom']         = post('nom');
        $old['type']        = post('type');
        $old['responsable'] = post('responsable');

        if ($old['nom'] === '')            { $errors['nom'] = 'Le nom de la structure est requis.'; }
        if (!isset($TYPES[$old['type']]))  { $errors['type'] = 'Type invalide.'; }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                if ($form_action === 'creer') {
                    $id  = generer_id_struct($pdo);
                    $num = (int) substr($id, 1);

                    $slug = preg_replace('/[^a-z0-9]+/', '', strtolower($old['nom']));
                    $base = $slug !== '' ? substr($slug, 0, 36) : ('struct' . $num);
                    $mail = $base . '@esp.sn';
                    $chk  = $pdo->prepare('SELECT 1 FROM STRUCTURE WHERE mail = :m');
                    $chk->execute([':m' => $mail]);
                    if ($chk->fetchColumn()) { $mail = $base . $num . '@esp.sn'; }
                    $tel  = '33' . str_pad((string) $num, 7, '0', STR_PAD_LEFT);

                    $pdo->prepare(
                        'INSERT INTO STRUCTURE (id_struct, nom_struct, desc_struct, mail, tel, type_struct)
                         VALUES (:id, :nom, NULL, :mail, :tel, :type)'
                    )->execute([':id' => $id, ':nom' => $old['nom'], ':mail' => $mail, ':tel' => $tel, ':type' => $old['type']]);
                } else {
                    $id = $old['id_struct'];
                    $pdo->prepare('UPDATE STRUCTURE SET nom_struct = :nom, type_struct = :type WHERE id_struct = :id')
                        ->execute([':nom' => $old['nom'], ':type' => $old['type'], ':id' => $id]);
                }

                // Responsable = gestionnaire rattaché à la structure.
                $pdo->prepare('DELETE FROM GESTIONNAIRE WHERE id_struct = :s')->execute([':s' => $id]);
                if ($old['responsable'] !== '') {
                    $pdo->prepare('INSERT INTO GESTIONNAIRE (matricule_user, id_struct) VALUES (:m, :s)')
                        ->execute([':m' => $old['responsable'], ':s' => $id]);
                }

                $pdo->commit();
                set_flash('succes', $form_action === 'creer' ? 'Structure créée.' : 'Structure mise à jour.');
                redirect('structures.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors['global'] = "Erreur lors de l'enregistrement.";
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
    $id = $_GET['id'] ?? '';
    $stmt = $pdo->prepare('SELECT id_struct, nom_struct, type_struct FROM STRUCTURE WHERE id_struct = :id');
    $stmt->execute([':id' => $id]);
    $d = $stmt->fetch();
    if ($d) {
        $resp = $pdo->prepare('SELECT matricule_user FROM GESTIONNAIRE WHERE id_struct = :s LIMIT 1');
        $resp->execute([':s' => $id]);
        $old = [
            'id_struct'   => $d['id_struct'],
            'nom'         => $d['nom_struct'],
            'type'        => $d['type_struct'],
            'responsable' => (string) ($resp->fetchColumn() ?: ''),
        ];
        $mode = 'modifier';
    } else {
        set_flash('erreur', 'Structure introuvable.');
        redirect('structures.php');
    }
}

$gestionnaires = $pdo->query(
    "SELECT matricule_user, CONCAT(prenom, ' ', nom) AS nom_complet
     FROM UTILISATEUR WHERE profil = 'GESTIONNAIRE' ORDER BY nom, prenom"
)->fetchAll();

$structures = $pdo->query(
    "SELECT s.id_struct, s.nom_struct, s.type_struct,
            (SELECT CONCAT(u.prenom, ' ', u.nom)
               FROM GESTIONNAIRE g JOIN UTILISATEUR u ON u.matricule_user = g.matricule_user
              WHERE g.id_struct = s.id_struct LIMIT 1) AS responsable,
            (SELECT COUNT(DISTINCT a.id_act)
               FROM GESTIONNAIRE g2 JOIN ACTIVITE a ON a.matricule_user = g2.matricule_user
              WHERE g2.id_struct = s.id_struct) AS nb_activites
     FROM STRUCTURE s
     ORDER BY s.type_struct, s.nom_struct"
)->fetchAll();
$nb = count($structures);

$flash = get_flash();

$page_active = 'structures';
$titre       = 'Structures';
$head_auto   = false;

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="users-head">
    <div class="users-head-left">
        <a class="back-btn" href="dashboard.php" aria-label="Retour au tableau de bord"><?= icone('chevron-left', 20) ?></a>
        <span class="back-label">Tableau de bord</span>
        <div class="users-title">
            <h1>Structures</h1>
            <p><?= $nb ?> structure<?= $nb > 1 ? 's' : '' ?></p>
        </div>
    </div>
    <a class="btn-aubergine" href="structures.php?action=nouveau">
        <?= icone('plus', 20) ?> Ajouter
    </a>
</div>

<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-error"><?= e($errors['global']) ?></div>
<?php endif; ?>

<?php if ($mode === 'nouveau' || $mode === 'modifier'): ?>
    <?php $estEdit = $mode === 'modifier'; ?>
    <section class="panel-card form-card-admin">
        <h2 class="form-card-title"><?= $estEdit ? 'Modifier la structure' : 'Nouvelle structure' ?></h2>

        <form method="post" action="structures.php" class="dept-form" novalidate>
            <input type="hidden" name="form_action" value="<?= $estEdit ? 'modifier' : 'creer' ?>">
            <?php if ($estEdit): ?>
                <input type="hidden" name="id_struct" value="<?= e($old['id_struct']) ?>">
            <?php endif; ?>

            <div class="field dept-field">
                <label class="field-label" for="nom">Nom</label>
                <input class="input <?= isset($errors['nom']) ? 'input-error' : '' ?>" type="text"
                       id="nom" name="nom" value="<?= e($old['nom']) ?>" required>
                <?php if (isset($errors['nom'])): ?><span class="field-msg"><?= e($errors['nom']) ?></span><?php endif; ?>
            </div>

            <div class="field dept-field">
                <label class="field-label" for="type">Type</label>
                <select class="input select <?= isset($errors['type']) ? 'input-error' : '' ?>" id="type" name="type">
                    <?php foreach ($TYPES as $val => $lib): ?>
                        <option value="<?= $val ?>" <?= $old['type'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['type'])): ?><span class="field-msg"><?= e($errors['type']) ?></span><?php endif; ?>
            </div>

            <div class="field dept-field">
                <label class="field-label" for="responsable">Responsable</label>
                <select class="input select" id="responsable" name="responsable">
                    <option value="">— Aucun —</option>
                    <?php foreach ($gestionnaires as $g): ?>
                        <option value="<?= e($g['matricule_user']) ?>" <?= $old['responsable'] === $g['matricule_user'] ? 'selected' : '' ?>>
                            <?= e($g['nom_complet']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="dept-form-actions">
                <a class="btn-outline" href="structures.php">Annuler</a>
                <button type="submit" class="btn-aubergine"><?= $estEdit ? 'Enregistrer' : 'Créer' ?></button>
            </div>
        </form>
    </section>

<?php else: ?>

    <?php if (empty($structures)): ?>
        <section class="panel-card"><p class="recent-empty">Aucune structure pour le moment.</p></section>
    <?php else: ?>
        <section class="dept-grid">
            <?php foreach ($structures as $d):
                $typeLib = $TYPES[$d['type_struct']] ?? $d['type_struct'];
                $typeCls = $TYPECLS[$d['type_struct']] ?? 'type-dep'; ?>
                <div class="dept-card">
                    <div class="dept-card-top">
                        <span class="dept-ico"><?= icone('building', 24) ?></span>
                        <div class="row-actions">
                            <a class="act-btn" href="structures.php?action=modifier&id=<?= urlencode($d['id_struct']) ?>"
                               aria-label="Modifier"><?= icone('edit', 18) ?></a>
                            <form method="post" action="structures.php" class="inline-form"
                                  onsubmit="return confirm('Supprimer la structure « <?= e($d['nom_struct']) ?> » ?');">
                                <input type="hidden" name="form_action" value="supprimer">
                                <input type="hidden" name="id_struct" value="<?= e($d['id_struct']) ?>">
                                <button type="submit" class="act-btn act-danger" aria-label="Supprimer"><?= icone('trash', 18) ?></button>
                            </form>
                        </div>
                    </div>
                    <h3 class="dept-name"><?= e($d['nom_struct']) ?></h3>
                    <span class="struct-type <?= $typeCls ?>"><?= e($typeLib) ?></span>
                    <p class="dept-resp"><?= e($d['responsable'] ?? '') ?: 'Non assigné' ?></p>
                    <p class="dept-count"><strong><?= (int) $d['nb_activites'] ?></strong> activités planifiées</p>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

<?php endif; ?>

<?php if ($flash): ?>
    <div class="adm-toast adm-toast-<?= $flash['type'] === 'erreur' ? 'error' : 'success' ?>" id="admToast" role="status">
        <?= icone($flash['type'] === 'erreur' ? 'alert-circle' : 'check-circle', 20) ?>
        <span><?= e($flash['message']) ?></span>
        <button type="button" class="adm-toast-x" aria-label="Fermer"><?= icone('x', 16) ?></button>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
