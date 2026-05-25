<?php

require_once __DIR__ . '/Model.php';

class Activity extends Model
{
    protected static string $table = 'activities';

    /**
     * Alle Tätigkeiten zu einem Auftrag, mit Name des Mitarbeiters.
     */
    public static function findByOrder(int $orderId): array
    {
        $stmt = static::db()->prepare("
            SELECT activities.*, users.name AS user_name
            FROM activities
            JOIN users ON activities.user_id = users.id
            WHERE activities.order_id = ?
            ORDER BY activities.worked_at DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Alle Tätigkeiten eines Mitarbeiters — auch auftragsunabhängige.
     */
    public static function findByUser(int $userId): array
    {
        $stmt = static::db()->prepare("
            SELECT activities.*, orders.title AS order_title
            FROM activities
            LEFT JOIN orders ON activities.order_id = orders.id
            WHERE activities.user_id = ?
            ORDER BY activities.worked_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
