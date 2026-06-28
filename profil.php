<?php
/**
 * profil.php  (racine du projet)
 * Page de gestion du profil de l'utilisateur connecté.
 * Accessible par TOUS les profils (Utilisateur/Étudiant, Personnel, Gestionnaire, Admin)
 * via l'avatar présent dans la barre de navigation de chaque espace.
 *
 * L'utilisateur peut modifier : prénom, nom, téléphone, mot de passe.
 * L'email n'est PAS modifiable (affiché en lecture seule).
 */

session_start();
require_once __DIR__ . '/Gestionnaire/models/utilisateur.php'; // charge aussi connexionBD()

// Identifie l'utilisateur connecté (clé posée par la connexion, commune à tous les espaces).
$matricule = $_SESSION['matricule_user'] ?? null;
if (!$matricule) {
    header('Location: /GestionDesActiviteEsp/index.php');
    exit;
}

$pdo = connexionBD();
$u   = getUtilisateurParMatricule($pdo, $matricule);
if (!$u) {
    session_destroy();
    header('Location: /GestionDesActiviteEsp/index.php');
    exit;
}

function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

$errors  = [];
$success = false;
$old = [
    'prenom' => $u['prenom'],
    'nom'    => $u['nom'],
    'tel'    => $u['tel'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Le prénom et le nom ne sont PAS modifiables : on conserve les valeurs de la base.
    $old['tel']    = trim($_POST['tel'] ?? '');
    $mdp  = $_POST['mot_de_passe']  ?? '';
    $mdp2 = $_POST['mot_de_passe2'] ?? '';

    // Téléphone optionnel : format + unicité (en excluant son propre compte).
    if ($old['tel'] !== '') {
        if (!preg_match('/^[0-9+ ]{6,20}$/', $old['tel'])) {
            $errors['tel'] = 'Numéro de téléphone invalide.';
        } else {
            $st = $pdo->prepare('SELECT 1 FROM UTILISATEUR WHERE tel = :t AND matricule_user <> :m');
            $st->execute([':t' => $old['tel'], ':m' => $matricule]);
            if ($st->fetchColumn()) {
                $errors['tel'] = 'Ce numéro de téléphone est déjà utilisé.';
            }
        }
    }

    // Mot de passe : optionnel ; si rempli, au moins 4 caractères et confirmation identique.
    $changePwd = ($mdp !== '' || $mdp2 !== '');
    if ($changePwd) {
        if (strlen($mdp) < 4) {
            $errors['mot_de_passe'] = 'Le mot de passe doit faire au moins 4 caractères.';
        } elseif ($mdp !== $mdp2) {
            $errors['mot_de_passe2'] = 'Les deux mots de passe ne correspondent pas.';
        }
    }

    if (empty($errors)) {
        // L'email, le profil et le niveau d'accès ne sont PAS modifiables ici : on les renvoie inchangés.
        $telVal = ($old['tel'] === '') ? null : $old['tel'];
        $ok = modifierUtilisateur(
            $pdo,
            $matricule,
            $old['nom'],
            $old['prenom'],
            $u['email'],
            $telVal,
            $u['profil'],
            (int) $u['niveau_acces']
        );
        if ($ok && $changePwd) {
            $ok = modifierMotDePasse($pdo, $matricule, $mdp);
        }
        if ($ok) {
            // Met à jour la session pour que l'avatar / le nom affichés soient corrects.
            $_SESSION['nom']    = $old['nom'];
            $_SESSION['prenom'] = $old['prenom'];
            $success = true;
            $u = getUtilisateurParMatricule($pdo, $matricule);
        } else {
            $errors['global'] = "Une erreur est survenue lors de l'enregistrement.";
        }
    }
}

$initiales = strtoupper(mb_substr($old['prenom'], 0, 1) . mb_substr($old['nom'], 0, 1));
$roleLib = [
    'ADMIN'        => 'Administrateur',
    'GESTIONNAIRE' => 'Gestionnaire',
    'PERSONNEL'    => 'Personnel',
    'ETUDIANT'     => 'Étudiant',
][$u['profil']] ?? $u['profil'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .esp-bg { background-color: #4A0E4E; }
        .esp-tx { color: #4A0E4E; }
        .esp-ring:focus { outline: none; box-shadow: 0 0 0 2px #4A0E4E33; border-color: #4A0E4E; }
    </style>
</head>

<body class="min-h-screen bg-[#FDFBFD] font-sans antialiased text-gray-800">

    <div class="max-w-2xl mx-auto px-4 py-10">

        <!-- Retour -->
        <button onclick="history.back()"
            class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-[#4A0E4E] transition">
            <span class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center">‹</span>
            Retour
        </button>

        <!-- En-tête profil -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="esp-bg px-8 py-7 flex items-center gap-4 text-white">
                <div class="w-16 h-16 rounded-full bg-[#D4AF37] text-[#4A0E4E] flex items-center justify-center text-xl font-black">
                    <?= h($initiales) ?>
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight"><?= h($old['prenom'] . ' ' . strtoupper($old['nom'])) ?></h1>
                    <p class="text-white/70 text-sm font-medium"><?= h($roleLib) ?> · <?= h($u['matricule_user']) ?></p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            Profil mis à jour avec succès.
        </div>
        <?php endif; ?>
        <?php if (!empty($errors['global'])): ?>
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700">
            <?= h($errors['global']) ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="post" action="profil.php"
            class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">

            <h2 class="text-lg font-black esp-tx">Informations personnelles</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="prenom">
                        Prénom <span class="font-normal text-gray-400">(non modifiable)</span>
                    </label>
                    <input id="prenom" type="text" value="<?= h($old['prenom']) ?>" disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm text-gray-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="nom">
                        Nom <span class="font-normal text-gray-400">(non modifiable)</span>
                    </label>
                    <input id="nom" type="text" value="<?= h($old['nom']) ?>" disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm text-gray-500 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="email">
                        Email <span class="font-normal text-gray-400">(non modifiable)</span>
                    </label>
                    <input id="email" type="email" value="<?= h($u['email']) ?>" disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-sm text-gray-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="tel">
                        Téléphone <span class="font-normal text-gray-400">(optionnel)</span>
                    </label>
                    <input id="tel" name="tel" type="tel" value="<?= h($old['tel']) ?>" placeholder="Ex : 770000000"
                        class="w-full px-4 py-2.5 rounded-xl border <?= isset($errors['tel']) ? 'border-red-400' : 'border-gray-200' ?> text-sm esp-ring">
                    <?php if (isset($errors['tel'])): ?><p class="text-xs text-red-600 mt-1"><?= h($errors['tel']) ?></p><?php endif; ?>
                </div>
            </div>

            <hr class="border-gray-100">

            <h2 class="text-lg font-black esp-tx">Changer le mot de passe</h2>
            <p class="text-xs text-gray-400 -mt-4">Laisse ces champs vides pour conserver ton mot de passe actuel.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="mot_de_passe">Nouveau mot de passe</label>
                    <input id="mot_de_passe" name="mot_de_passe" type="password" autocomplete="new-password"
                        class="w-full px-4 py-2.5 rounded-xl border <?= isset($errors['mot_de_passe']) ? 'border-red-400' : 'border-gray-200' ?> text-sm esp-ring">
                    <?php if (isset($errors['mot_de_passe'])): ?><p class="text-xs text-red-600 mt-1"><?= h($errors['mot_de_passe']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5" for="mot_de_passe2">Confirmer</label>
                    <input id="mot_de_passe2" name="mot_de_passe2" type="password" autocomplete="new-password"
                        class="w-full px-4 py-2.5 rounded-xl border <?= isset($errors['mot_de_passe2']) ? 'border-red-400' : 'border-gray-200' ?> text-sm esp-ring">
                    <?php if (isset($errors['mot_de_passe2'])): ?><p class="text-xs text-red-600 mt-1"><?= h($errors['mot_de_passe2']) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="history.back()"
                    class="px-6 py-2.5 rounded-full border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-7 py-2.5 rounded-full esp-bg text-white text-sm font-bold hover:opacity-90 transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</body>

</html>
