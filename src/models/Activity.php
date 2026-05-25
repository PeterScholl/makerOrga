<?php

class Activity extends Model
{
    protected static string $table = 'activities';

    /**
     * Alle Tätigkeiten zu einem Auftrag, mit kommagetrennten Mitarbeiternamen.
     * GROUP_CONCAT fasst mehrere Mitarbeiter einer Tätigkeit zusammen.
     */
    public static function findByOrder(int $orderId): array
    {
        $stmt = static::db()->prepare("
            SELECT activities.*, GROUP_CONCAT(users.name, ', ') AS user_names
            FROM activities
            JOIN activity_users ON activities.id = activity_users.activity_id
            JOIN users          ON activity_users.user_id = users.id
            WHERE activities.order_id = ?
            GROUP BY activities.id
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
            JOIN activity_users ON activities.id = activity_users.activity_id
            LEFT JOIN orders    ON activities.order_id = orders.id
            WHERE activity_users.user_id = ?
            ORDER BY activities.worked_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Tätigkeit anlegen und Mitarbeiter in der Zwischentabelle eintragen.
     * $data muss 'user_ids' als Array enthalten.
     */
    public static function create(array $data): int
    {
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $id = parent::create($data);

        $stmt = static::db()->prepare(
            'INSERT INTO activity_users (activity_id, user_id) VALUES (?, ?)'
        );
        foreach ($userIds as $userId) {
            $stmt->execute([$id, (int) $userId]);
        }

        return $id;
    }

    /**
     * Tätigkeit und Mitarbeiterzuordnung aktualisieren.
     * Bestehende Zuordnungen werden vollständig ersetzt.
     */
    public static function updateWithUsers(int $id, array $data, array $userIds): void
    {
        static::update($id, $data);

        static::db()->prepare('DELETE FROM activity_users WHERE activity_id = ?')
                    ->execute([$id]);

        $stmt = static::db()->prepare(
            'INSERT INTO activity_users (activity_id, user_id) VALUES (?, ?)'
        );
        foreach ($userIds as $userId) {
            $stmt->execute([$id, (int) $userId]);
        }
    }
}
