<?php
// api_register.php
header('Content-Type: application/json');
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["error" => "Données invalides."]);
    exit;
}

$nom = $input['nom'] ?? '';
$prenom = $input['prenom'] ?? '';
$email = $input['email'] ?? '';
$tel = $input['tel'] ?? '';
$mot_de_passe = $input['mot_de_passe'] ?? '';
$profil = $input['profil'] ?? ''; // 'ETUDIANT' ou 'PERSONNEL'
$filiere = $input['filiere'] ?? '';
$niveau = $input['niveau'] ?? '';
$poste = $input['poste'] ?? '';
$specialite = $input['specialite'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Email invalide."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Génération automatique du matricule
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM UTILISATEUR");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $prochainNumero = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
    
    // Correction ici : ETU pour Étudiant, PER pour Personnel
    $prefixe = ($profil === 'ETUDIANT') ? 'ETU' : 'PER';
    $matricule = $prefixe . $prochainNumero;

    $hashedPassword = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    $sql = "INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, filiere, niveau, poste, specialite) 
            VALUES (:matricule, :nom, :prenom, :email, :tel, :password, :profil, :filiere, :niveau, :poste, :specialite)";
            
    $insertStmt = $pdo->prepare($sql);
    $insertStmt->execute([
        ':matricule' => $matricule,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':tel' => $tel,
        ':password' => $hashedPassword,
        ':profil' => $profil,
        ':filiere' => $filiere,
        ':niveau' => $niveau,
        ':poste' => $poste,
        ':specialite' => $specialite
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