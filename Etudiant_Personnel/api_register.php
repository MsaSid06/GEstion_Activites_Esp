<?php
// api_register.php
header('Content-Type: application/json');
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["error" => "Données invalides."]);
    exit;
}

$nom        = $input['nom'] ?? '';
$prenom     = $input['prenom'] ?? '';
$email      = $input['email'] ?? '';
$tel        = $input['tel'] ?? '';
$mot_de_passe = $input['mot_de_passe'] ?? '';
$profil     = $input['profil'] ?? '';
$filiere    = $input['filiere'] ?? '';
$niveau     = $input['niveau'] ?? '';
$poste      = $input['poste'] ?? '';
$specialite = $input['specialite'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Email invalide."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Génération du matricule unique par préfixe
    $prefixe = ($profil === 'ETUDIANT') ? 'ETU' : 'PER';

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM UTILISATEUR WHERE matricule_user LIKE :prefixe");
    $stmt->execute([':prefixe' => $prefixe . '%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $prochainNumero = str_pad($row['total'] + 1, 2, '0', STR_PAD_LEFT);
// → donne ETU01, ETU02, PER01, PER02...
    $matricule = $prefixe . $prochainNumero;

    // Vérifier que le matricule n'existe pas déjà
    $check = $pdo->prepare("SELECT COUNT(*) FROM UTILISATEUR WHERE matricule_user = :matricule");
    $check->execute([':matricule' => $matricule]);
    if ($check->fetchColumn() > 0) {
        $maxStmt = $pdo->prepare("SELECT MAX(matricule_user) FROM UTILISATEUR WHERE matricule_user LIKE :prefixe");
        $maxStmt->execute([':prefixe' => $prefixe . '%']);
        $maxMatricule = $maxStmt->fetchColumn();
        $dernierNumero = intval(substr($maxMatricule, strlen($prefixe)));
        $matricule = $prefixe . str_pad($dernierNumero + 1, 4, '0', STR_PAD_LEFT);
    }

    $hashedPassword = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    $sql = "INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, filiere, niveau, poste, specialite) 
            VALUES (:matricule, :nom, :prenom, :email, :tel, :password, :profil, :filiere, :niveau, :poste, :specialite)";

    $insertStmt = $pdo->prepare($sql);
    $insertStmt->execute([
        ':matricule' => $matricule,
        ':nom'       => $nom,
        ':prenom'    => $prenom,
        ':email'     => $email,
        ':tel'       => $tel,
        ':password'  => $hashedPassword,
        ':profil'    => $profil,
        ':filiere'   => $filiere,
        ':niveau'    => $niveau,
        ':poste'     => $poste,
        ':specialite'=> $specialite
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Inscription réussie ! Votre matricule est : $matricule"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["error" => "Erreur lors de l'inscription : " . $e->getMessage()]);
}
?>
