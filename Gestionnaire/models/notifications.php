<?php

function notifications_recentes(PDO $pdo, string $matricule): array
{
    $stmt = $pdo->prepare(
        'SELECT n.id_not, n.message, n.date_envoi, n.id_act, a.titre AS activite
         FROM NOTIFICATION n
         LEFT JOIN ACTIVITE a ON a.id_act = n.id_act
         where id_emetteur = :id
         ORDER BY n.date_envoi DESC'
    );
    $stmt->bindValue(':id', $matricule);
    $stmt->execute();
    return $stmt->fetchAll();
}
function getAllNotifications(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT n.id_not, n.message, n.date_envoi, n.id_act, a.titre AS activite
         FROM NOTIFICATION n
         LEFT JOIN ACTIVITE a ON a.id_act = n.id_act
         ORDER BY n.date_envoi DESC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}


/**
 * Table NOTIFICATION
 * PK : id_not (auto-increment)
 * FK : id_emetteur -> UTILISATEUR(matricule_user)
 * FK : id_act -> ACTIVITE(id_act) ON DELETE SET NULL
 */

function creerNotification(
    PDO $pdo,
    string $id_emetteur,
    ?int $id_act,
    string $message
): bool {

    $sql = "INSERT INTO NOTIFICATION (id_emetteur, id_act, message)
            VALUES (:id_emetteur, :id_act, :message)";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id_emetteur', $id_emetteur, PDO::PARAM_STR);
        $stmt->bindValue(':id_act', $id_act, $id_act === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':message', $message, PDO::PARAM_STR);

        return $stmt->execute();

    } catch (PDOException $e) {
        error_log('creerNotification: ' . $e->getMessage());
        return false;
    }
}

function getNotificationParId(PDO $pdo, int $id_not): array|false
{
    $stmt = $pdo->prepare("SELECT * FROM NOTIFICATION WHERE id_not = :id_not");
    $stmt->bindValue(':id_not', $id_not, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch() ?: false;
}

function getNotificationsParUtilisateur(PDO $pdo, string $id_emetteur): array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM NOTIFICATION
        WHERE id_emetteur = :id_emetteur
        ORDER BY date_envoi DESC
    ");

    $stmt->bindValue(':id_emetteur', $id_emetteur, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll();
}

function getToutesNotifications(PDO $pdo): array
{
    return $pdo->query("
        SELECT *
        FROM NOTIFICATION
        ORDER BY date_envoi DESC
    ")->fetchAll();
}

function supprimerNotification(PDO $pdo, int $id_not): bool
{
    $stmt = $pdo->prepare("DELETE FROM NOTIFICATION WHERE id_not = :id_not");
    $stmt->bindValue(':id_not', $id_not, PDO::PARAM_INT);

    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('supprimerNotification: ' . $e->getMessage());
        return false;
    }
}

function supprimerNotificationsParActivite(PDO $pdo, int $id_act): bool
{
    $stmt = $pdo->prepare("DELETE FROM NOTIFICATION WHERE id_act = :id_act");
    $stmt->bindValue(':id_act', $id_act, PDO::PARAM_INT);

    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('supprimerNotificationsParActivite: ' . $e->getMessage());
        return false;
    }
}
