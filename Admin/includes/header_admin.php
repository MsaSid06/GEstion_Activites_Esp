<?php
/**
 * includes/header_admin.php
 * En-tête commun aux pages d'administration + volet de notifications.
 *
 * La page appelante doit, avant l'inclusion :
 *   - avoir appelé exiger_profil(['ADMIN'])
 *   - disposer de $pdo (config/database.php inclus)
 *   - définir $page_active, $titre (titre du document)
 * Optionnel :
 *   - $sous_titre, $head_auto (false pour ne pas afficher l'en-tête de page auto)
 */

require_once './icons.php';
require_once './notifications.php';

$user        = utilisateur_courant();
$page_active = $page_active ?? '';
$titre       = $titre ?? '';
$sous_titre  = $sous_titre ?? '';
$head_auto   = $head_auto ?? true;

$notifications = isset($pdo) ? notifications_recentes($pdo, 10) : [];
$notif_count   = isset($pdo) ? compter_notifications($pdo) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre) ?> — Administration ESP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<header class="adm-header">
    <div class="adm-brand">
        <div class="adm-logo">ESP</div>
        <div class="adm-brand-text">
            <strong>ESP Dakar</strong>
            <span>Administrateur</span>
        </div>
    </div>

    <div class="adm-header-right">
        <div class="adm-bell-wrap">
            <button type="button" class="adm-bell" id="admBell" aria-label="Notifications" aria-expanded="false">
                <?= icone('bell', 24) ?>
                <?php if ($notif_count > 0): ?>
                    <span class="adm-bell-badge" id="admBellBadge"><?= (int) $notif_count ?></span>
                <?php endif; ?>
            </button>

            <div class="adm-notif" id="admNotif" hidden>
                <div class="adm-notif-head">
                    <div class="adm-notif-title">
                        Notifications
                        <span class="adm-notif-count"><?= (int) $notif_count ?></span>
                    </div>
                    <div class="adm-notif-tools">
                        <button type="button" class="adm-notif-readall" id="admNotifReadAll">Tout lire</button>
                        <button type="button" class="adm-notif-close" id="admNotifClose" aria-label="Fermer">
                            <?= icone('x', 18) ?>
                        </button>
                    </div>
                </div>

                <div class="adm-notif-body">
                    <?php if (empty($notifications)): ?>
                        <p class="adm-notif-empty">Aucune notification.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="adm-notif-item">
                                <span class="adm-notif-ico"><?= icone('bell', 18) ?></span>
                                <div class="adm-notif-text">
                                    <?php if (!empty($n['activite'])): ?>
                                        <strong><?= e($n['activite']) ?></strong>
                                    <?php endif; ?>
                                    <p><?= e($n['message']) ?></p>
                                    <span class="adm-notif-time"><?= e(temps_relatif($n['date_envoi'])) ?></span>
                                </div>
                                <span class="adm-notif-dot" aria-hidden="true"></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <a class="adm-logout" href="../auth/logout.php">
            <?= icone('logout', 22) ?>
            <span>Déconnexion</span>
        </a>
    </div>
</header>

<main class="adm-main">
    <?php if ($head_auto && $titre !== ''): ?>
        <div class="adm-pagehead">
            <h1><?= e($titre) ?></h1>
            <?php if ($sous_titre !== ''): ?>
                <p><?= e($sous_titre) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
