<?php

class Order extends Model
{
    protected static string $table = 'orders';

    /**
     * Alle Aufträge mit Kundenname und zugewiesenem Mitarbeiter in einer Abfrage.
     * JOIN vermeidet N+1-Abfragen (für jeden Auftrag einzeln nachfragen).
     */
    public static function findAllWithRelations(): array
    {
        $sql = "
            SELECT
                orders.*,
                customers.name  AS customer_name,
                users.name      AS assigned_user_name
            FROM orders
            LEFT JOIN customers ON orders.customer_id    = customers.id
            LEFT JOIN users     ON orders.assigned_user_id = users.id
            ORDER BY orders.received_at DESC
        ";
        return static::db()->query($sql)->fetchAll();
    }

    public static function markAsReturned(int $id): void
    {
        static::update($id, ['returned' => 1]);
    }
}
