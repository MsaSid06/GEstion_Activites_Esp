<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table STRUCTURE
 * PK : id_struct (char(5), fourni par l'appelant)
 */

function creerStructure(
    PDO $pdo,
    string $id_struct,
    string $nom_struct,
    ?string $desc_struct,
    string $email,
    string $tel,
    string $type_struct
): bool {
    $sql = "INSERT INTO STRUCTURE (id_struct, nom_struct, desc_struct, email, tel, type_struct)
            VALUES (:id_struct, :nom_struct, :desc_struct, :email, :tel, :type_struct)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
        $stmt->bindValue(':nom_struct', $nom_struct, PDO::PARAM_STR);
        $stmt->bindValue(':desc_struct', $desc_struct, $desc_struct === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':tel', $tel, PDO::PARAM_STR);
        $stmt->bindValue(':type_struct', $type_struct, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('creerStructure: ' . $e->getMessage());
        return false;
    }
}

function getStructureParId(PDO $pdo, string $id_struct): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM STRUCTURE WHERE id_struct = :id_struct");
    $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: false;
}

function getToutesStructures(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM STRUCTURE ORDER BY nom_struct")->fetchAll();
}

function modifierStructure(
    PDO $pdo,
    string $id_struct,
    string $nom_struct,
    ?string $desc_struct,
    string $email,
    string $tel,
    string $type_struct
): bool {
    $sql = "UPDATE STRUCTURE
            SET nom_struct = :nom_struct, desc_struct = :desc_struct, email = :email,
                tel = :tel, type_struct = :type_struct
            WHERE id_struct = :id_struct";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nom_struct', $nom_struct, PDO::PARAM_STR);
        $stmt->bindValue(':desc_struct', $desc_struct, $desc_struct === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':tel', $tel, PDO::PARAM_STR);
        $stmt->bindValue(':type_struct', $type_struct, PDO::PARAM_STR);
        $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('modifierStructure: ' . $e->getMessage());
        return false;
    }
}

function supprimerStructure(PDO $pdo, string $id_struct): bool
{
    // ATTENTION :
    // - APPARTENIR est en ON DELETE CASCADE -> supprimé automatiquement.
    // - GESTIONNAIRE n'a PAS de ON DELETE défini (= RESTRICT par défaut)
    //   -> la suppression échoue si un gestionnaire est encore rattaché.
    $stmt = $pdo->prepare("DELETE FROM STRUCTURE WHERE id_struct = :id_struct");
    $stmt->bindValue(':id_struct', $id_struct, PDO::PARAM_STR);
    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('supprimerStructure: ' . $e->getMessage());
        return false;
    }
}