<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table PERSONNEL
 * PK : id_personnel (auto_increment) | matricule_user unique (FK ON DELETE CASCADE)
 */

function creerPersonnel(PDO $pdo, string $matricule_user, string $poste, ?string $specialite = null): int|false
{
    $sql = "INSERT INTO PERSONNEL (matricule_user, poste, specialite) VALUES (:matricule_user, :poste, :specialite)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':poste', $poste, PDO::PARAM_STR);
        $stmt->bindValue(':specialite', $specialite, $specialite === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('creerPersonnel: ' . $e->getMessage());
        return false;
    }
}

function getPersonnelParMatricule(PDO $pdo, string $matricule_user): array|false
{
    $stmt = $pdo->prepare(
        "SELECT p.*, u.nom, u.prenom, u.email
         FROM PERSONNEL p
         JOIN UTILISATEUR u ON u.matricule_user = p.matricule_user
         WHERE p.matricule_user = :matricule_user"
    );
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getToutPersonnel(PDO $pdo): array
{
    return $pdo->query(
        "SELECT p.*, u.nom, u.prenom, u.email
         FROM PERSONNEL p
         JOIN UTILISATEUR u ON u.matricule_user = p.matricule_user
         ORDER BY u.nom, u.prenom"
    )->fetchAll();
}

function modifierPersonnel(PDO $pdo, string $matricule_user, string $poste, ?string $specialite): bool
{
    $stmt = $pdo->prepare(
        "UPDATE PERSONNEL SET poste = :poste, specialite = :specialite WHERE matricule_user = :matricule_user"
    );
    $stmt->bindValue(':poste', $poste, PDO::PARAM_STR);
    $stmt->bindValue(':specialite', $specialite, $specialite === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    return $stmt->execute();
}

function supprimerPersonnel(PDO $pdo, string $matricule_user): bool
{
    $stmt = $pdo->prepare("DELETE FROM PERSONNEL WHERE matricule_user = :matricule_user");
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    return $stmt->execute();
}
