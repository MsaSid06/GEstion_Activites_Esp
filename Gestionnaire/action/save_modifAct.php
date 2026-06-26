<?php

require_once "../config/connexion.php";
require_once "../models/activite.php";
$pdo = connexionBD();

function nettoyer(?string $valeur): string
{
    return htmlspecialchars(trim($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

$form_action = nettoyer($_POST['form_action'] ?? '');
$id_act      = isset($_POST['id_act']) ? (int) $_POST['id_act'] : null; // entier, pas de htmlspecialchars sur un ID
$titre       = nettoyer($_POST['titre'] ?? '');
$lieu        = nettoyer($_POST['lieu'] ?? '');
$type_act    = nettoyer($_POST['type_act'] ?? '');
$date_debut  = nettoyer($_POST['date_debut'] ?? '');
$date_fin    = nettoyer($_POST['date_fin'] ?? '');
$description = nettoyer($_POST['description'] ?? '');
global $result;
if (isset($_POST["form_action"])) {
    $result = modifierActivite($pdo, $id_act, $titre, $description, $type_act, $date_debut, $date_fin, $lieu);
}
?>
<?php if ($result): ?>
<script>
    alert("Modification effectuée");
    window.location.href = "./mesActivites.php";
</script>
<?php else: ?>
<script>
    alert("Erreur lors de la modification: veuillez resaisir les bonnes valeures");
    window.location.href = './modifierActiviter.form';
</script>
<?php endif; ?>