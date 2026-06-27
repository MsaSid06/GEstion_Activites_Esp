<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table UTILISATEUR
 * PK : matricule_user (char(5), fourni par l'appelant, PAS auto-incrémenté)
 * Contraintes : email unique + format, tel unique, profil dans une liste fixe,
 *               niveau_acces (int, défaut 0)
 */

function creerUtilisateur(
    PDO $pdo,
    string $matricule_user,
    string $nom,
    string $prenom,
    string $email,
    ?string $tel,
    string $mot_de_passe,
    string $profil,
    int $niveau_acces = 1
): bool {
    $sql = "INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, niveau_acces)
            VALUES (:matricule_user, :nom, :prenom, :email, :tel, :mot_de_passe, :profil, :niveau_acces)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':tel', $tel, $tel === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':mot_de_passe', password_hash($mot_de_passe, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $stmt->bindValue(':profil', $profil, PDO::PARAM_STR);
        $stmt->bindValue(':niveau_acces', $niveau_acces, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        // ex : email/tel déjà utilisé, profil hors liste autorisée
        error_log('creerUtilisateur: ' . $e->getMessage());
        return false;
    }
}

function getUtilisateurParMatricule(PDO $pdo, string $matricule_user): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM UTILISATEUR WHERE matricule_user = :matricule_user");
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getUtilisateurParMail(PDO $pdo, string $mail): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM UTILISATEUR WHERE email = :email");
    $stmt->bindValue(':email', $mail, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getTousUtilisateurs(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM UTILISATEUR ORDER BY nom, prenom")->fetchAll();
}

function modifierUtilisateur(
    PDO $pdo,
    string $matricule_user,
    string $nom,
    string $prenom,
    string $email,
    ?string $tel,
    string $profil,
    int $niveau_acces
): bool {
    $sql = "UPDATE UTILISATEUR
            SET nom = :nom, prenom = :prenom, email = :email, tel = :tel,
                profil = :profil, niveau_acces = :niveau_acces
            WHERE matricule_user = :matricule_user";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':tel', $tel, $tel === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':profil', $profil, PDO::PARAM_STR);
        $stmt->bindValue(':niveau_acces', $niveau_acces, PDO::PARAM_INT);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('modifierUtilisateur: ' . $e->getMessage());
        return false;
    }
}

// Fonction séparée pour le mot de passe : évite de l'écraser par erreur
// quand on modifie juste les infos du profil.
function modifierMotDePasse(PDO $pdo, string $matricule_user, string $nouveau_mot_de_passe): bool
{
    $stmt = $pdo->prepare("UPDATE UTILISATEUR SET mot_de_passe = :mdp WHERE matricule_user = :matricule_user");
    $stmt->bindValue(':mdp', password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT), PDO::PARAM_STR);
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    return $stmt->execute();
}

function supprimerUtilisateur(PDO $pdo, string $matricule_user): bool
{
    // ATTENTION :
    // - ETUDIANT, PERSONNEL, GESTIONNAIRE, APPARTENIR sont en ON DELETE CASCADE
    //   -> supprimés automatiquement avec l'utilisateur.
    // - ACTIVITE et NOTIFICATION (id_emetteur) n'ont PAS de ON DELETE défini
    //   (= RESTRICT par défaut) -> la suppression échoue si l'utilisateur a
    //   créé une activité ou une notification.
    $stmt = $pdo->prepare("DELETE FROM UTILISATEUR WHERE matricule_user = :matricule_user");
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);

    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('supprimerUtilisateur: ' . $e->getMessage());
        return false;
    }
}
