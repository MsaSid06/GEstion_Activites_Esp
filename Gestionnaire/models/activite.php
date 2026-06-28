<?php

// declare(strict_types=1);

require_once __DIR__ . '/../config/connexion.php';

/**
 * Table ACTIVITE
 * PK : id_act (auto_increment)
 * FK : matricule_user -> UTILISATEUR(matricule_user)
 */

function creerActivite(
    PDO $pdo,
    string $matricule_user,
    string $titre,
    string $description,
    string $type_act,
    string $date_debut,
    string $date_fin,
    string $lieu
): int|false {
    $sql = "INSERT INTO ACTIVITE
            (matricule_user, titre, description, type_act, date_debut, date_fin, lieu)
            VALUES
            (:matricule_user, :titre, :description, :type_act, :date_debut, :date_fin, :lieu)";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':matricule_user', $matricule_user, PDO::PARAM_STR);
        $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':type_act', $type_act, PDO::PARAM_STR);
        $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        $stmt->bindValue(':lieu', $lieu, PDO::PARAM_STR);

        $stmt->execute();

        return (int) $pdo->lastInsertId();

    } catch (PDOException $e) {
        error_log('creerActivite : ' . $e->getMessage());
        return false;
    }
}

function getActiviteParId(PDO $pdo, int $id_act): array|false
{
    $stmt = $pdo->prepare(
        "SELECT a.*, u.nom, u.prenom
         FROM ACTIVITE a
         JOIN UTILISATEUR u
         ON u.matricule_user = a.matricule_user
         WHERE a.id_act = :id_act"
    );

    $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch() ?: false;
}

function getToutesActivites(PDO $pdo): array
{
    return $pdo->query(
        "SELECT a.*, u.nom, u.prenom
         FROM ACTIVITE a
         JOIN UTILISATEUR u
         ON u.matricule_user = a.matricule_user
         ORDER BY a.date_debut ASC"
    )->fetchAll();
}

function getToutesActivitesGestionnaire(PDO $pdo, string $gestMat): array
{
    return $pdo->query(
        "SELECT * FROM activite WHERE matricule_user = '$gestMat'"
    )->fetchAll();
}
function getDerniereActivite(PDO $pdo, string $gestMat): array|false
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM activite
        WHERE matricule_user = :matricule
        ORDER BY id_act DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':matricule' => $gestMat
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function modifierActivite(
    PDO $pdo,
    int $id_act,
    string $titre,
    string $description,
    string $type_act,
    string $date_debut,
    string $date_fin,
    string $lieu
): bool {

    $stmt = $pdo->prepare(
        "UPDATE ACTIVITE
         SET titre = :titre,
             description = :description,
             type_act = :type_act,
             date_debut = :date_debut,
             date_fin = :date_fin,
             lieu = :lieu
         WHERE id_act = :id_act"
    );

    $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);
    $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':type_act', $type_act, PDO::PARAM_STR);
    $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
    $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
    $stmt->bindValue(':lieu', $lieu, PDO::PARAM_STR);

    return $stmt->execute();
}

function supprimerActivite(PDO $pdo, int $id_act): bool
{
    $stmt = $pdo->prepare(
        "DELETE FROM ACTIVITE
         WHERE id_act = :id_act"
    );

    $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);

    return $stmt->execute();
}
