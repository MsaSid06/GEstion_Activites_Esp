<?php
/**
 * includes/notifications.php
 * Récupération des notifications pour le volet de l'en-tête.
 */

require_once __DIR__ . '/functions.php';

/**
 * Notifications les plus récentes (avec le titre de l'activité liée si présente).
 */
function notifications_recentes(PDO $pdo, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT n.id_not, n.message, n.date_envoi, n.id_act, a.titre AS activite
         FROM NOTIFICATION n
         LEFT JOIN ACTIVITE a ON a.id_act = n.id_act
         ORDER BY n.date_envoi DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Nombre total de notifications.
 */
function compter_notifications(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM NOTIFICATION')->fetchColumn();
}
