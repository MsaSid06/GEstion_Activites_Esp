<?php
session_start();
require_once "../config/connexion.php";
require_once "../models/activite.php";
require_once "../models/notifications.php";
$pdo = connexionBD();

function nettoyer(?string $valeur): string
{
    return htmlspecialchars(trim($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

$form_action = nettoyer($_POST['form_action'] ?? '');
$titre       = nettoyer($_POST['titre'] ?? '');
$lieu        = nettoyer($_POST['lieu'] ?? '');
$type_act    = nettoyer($_POST['type_act'] ?? '');
$date_debut  = nettoyer($_POST['date_debut'] ?? '');
$date_fin    = nettoyer($_POST['date_fin'] ?? '');
$description = nettoyer($_POST['description'] ?? '');
$notif = nettoyer($_POST['notif'] ?? '');
global $result;

$bon = true;
if (isset($_POST["form_action"])) {
    $result = creerActivite($pdo, $_SESSION['matricule_user'], $titre, $description, $type_act, $date_debut, $date_fin, $lieu);
    if($notif) {
        $dernier_act = getDerniereActivite($pdo, $_SESSION['matricule_user']);
        $id_act = $dernier_act['id_act'];
        $res_notif = creerNotification($pdo, $_SESSION['matricule_user'], $id_act, $notif);
        $bon = $res_notif;
    }
}
?>
<?php if ($result && $bon): ?>
<script>
    alert("Activités creer avec succes");
    window.location.href = "./mesActivites.php";
</script>
<?php else: ?>
<script>
    alert("Erreur lors de la creation: veuillez resaisir les bonnes valeures");
    window.location.href = "./formCreationActivite.php";
</script>
<?php endif; ?>