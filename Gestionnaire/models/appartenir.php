<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table APPARTENIR (table d'association UTILISATEUR <-> STRUCTURE)
 * PK : id_appartenir (auto_increment)
 */

function creerAppartenance(PDO $pdo, string $matricule_user, string $id_struct): int|false
{
    $sql = "INSERT INTO APPARTENIR (matricule_user, id_struct) VALUES (:matricule_user, :id_struct)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('creerAppartenance: ' . $e->getMessage());
        return false;
    }
}

function getAppartenanceParId(PDO $pdo, int $id_appartenir): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM APPARTENIR WHERE id_appartenir = :id");
    $stmt->bindValue(':id', $id_appartenir, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getStructuresParUtilisateur(PDO $pdo, string $matricule_user): array
{
    $stmt = $pdo->prepare(
        "SELECT s.* FROM APPARTENIR a
         JOIN STRUCTURE s ON s.id_struct = a.id_struct
         WHERE a.matricule_user = :matricule_user"
    );
    $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUtilisateursParStructure(PDO $pdo, string $id_struct): array
{
    $stmt = $pdo->prepare(
        "SELECT u.* FROM APPARTENIR a
         JOIN UTILISATEUR u ON u.matricule_user = a.matricule_user
         WHERE a.id_struct = :id_struct"
    );
    $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}

function modifierAppartenance(PDO $pdo, int $id_appartenir, string $matricule_user, string $id_struct): bool
{
    $sql = "UPDATE APPARTENIR SET matricule_user = :matricule_user, id_struct = :id_struct
            WHERE id_appartenir = :id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id_appartenir, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('modifierAppartenance: ' . $e->getMessage());
        return false;
    }
}

function supprimerAppartenance(PDO $pdo, int $id_appartenir): bool
{
    $stmt = $pdo->prepare("DELETE FROM APPARTENIR WHERE id_appartenir = :id");
    $stmt->bindValue(':id', $id_appartenir, PDO::PARAM_INT);
    return $stmt->execute();
}