<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table ETUDIANT
 * PK : id_etd (auto_increment) | matricule_user unique (FK ON DELETE CASCADE)
 */

function creerEtudiant(PDO $pdo, string $matricule_user, string $filiere, string $niveau): int|false
{
    $sql = "INSERT INTO ETUDIANT (matricule_user, filiere, niveau) VALUES (:matricule_user, :filiere, :niveau)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':filiere', $filiere, PDO::PARAM_STR);
        $stmt->bindValue(':niveau', $niveau, PDO::PARAM_STR);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        // ex : matricule_user déjà enregistré comme étudiant (contrainte unique)
        error_log('creerEtudiant: ' . $e->getMessage());
        return false;
    }
}

function getEtudiantParMatricule(PDO $pdo, string $matricule_user): array|false
{
    $stmt = $pdo->prepare(
        "SELECT e.*, u.nom, u.prenom, u.email
         FROM ETUDIANT e
         JOIN UTILISATEUR u ON u.matricule_user = e.matricule_user
         WHERE e.matricule_user = :matricule_user"
    );
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getTousEtudiants(PDO $pdo): array
{
    return $pdo->query(
        "SELECT e.*, u.nom, u.prenom, u.email
         FROM ETUDIANT e
         JOIN UTILISATEUR u ON u.matricule_user = e.matricule_user
         ORDER BY u.nom, u.prenom"
    )->fetchAll();
}

function modifierEtudiant(PDO $pdo, string $matricule_user, string $filiere, string $niveau): bool
{
    $stmt = $pdo->prepare(
        "UPDATE ETUDIANT SET filiere = :filiere, niveau = :niveau WHERE matricule_user = :matricule_user"
    );
    $stmt->bindValue(':filiere', $filiere, PDO::PARAM_STR);
    $stmt->bindValue(':niveau', $niveau, PDO::PARAM_STR);
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    return $stmt->execute();
}

function supprimerEtudiant(PDO $pdo, string $matricule_user): bool
{
    // Supprime uniquement le statut "étudiant" ; l'UTILISATEUR reste en base.
    $stmt = $pdo->prepare("DELETE FROM ETUDIANT WHERE matricule_user = :matricule_user");
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    return $stmt->execute();
}