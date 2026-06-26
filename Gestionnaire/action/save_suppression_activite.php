<?php

require_once "../config/connexion.php";
require_once "../models/activite.php";
$pdo = connexionBD();


$id_act = isset($_POST['id_act']) ? (int) $_POST['id_act'] : null; // entier, pas de htmlspecialchars sur un ID

global $result;
$result =  supprimerActivite($pdo, $id_act);
?>
<?php if ($result): ?>
<script>
    alert("Activiter supprimer avec succes ");
    window.location.href = "./mesActivites.php";
</script>
<?php else: ?>
<script>
    alert("Erreur lors de la suppression: veuillez reessayer");
    window.location.href = "./mesActivites.php";
</script>
<?php endif; ?>