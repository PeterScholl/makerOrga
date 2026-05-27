<?php

class Order extends Model
{
    protected static string $table = 'orders';

    /**
     * Alle Aufträge mit Kundenname und zugewiesenem Mitarbeiter in einer Abfrage.
     * JOIN vermeidet N+1-Abfragen (für jeden Auftrag einzeln nachfragen).
     */
    public static function findAllWithRelations(array $filters = [], string $sort = 'received_at', string $dir = 'desc'): array
    {
        // Map von GET-Parameter auf SQL-Ausdruck (verhindert SQL-Injection)
        $sortMap = [
            'id'                 => 'orders.id',
            'title'              => 'orders.title',
            'status'             => 'orders.status',
            'priority'           => 'orders.priority',
            'received_at'        => 'orders.received_at',
            'customer_name'      => 'customers.name',
            'assigned_user_name' => 'users.name',
        ];
        $allowedDir = ['asc', 'desc'];
        $sortExpr = $sortMap[$sort] ?? 'orders.received_at';
        $dir      = in_array($dir, $allowedDir, true) ? $dir : 'desc';

        $where  = [];
        $params = [];
        foreach (['status', 'priority', 'type'] as $field) {
            $values = array_values(array_filter((array) ($filters[$field] ?? [])));
            if (!empty($values)) {
                $phs = implode(',', array_fill(0, count($values), '?'));
                $where[] = "orders.$field IN ($phs)";
                array_push($params, ...$values);
            }
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                orders.*,
                customers.name AS customer_name,
                users.name     AS assigned_user_name
            FROM orders
            LEFT JOIN customers ON orders.customer_id      = customers.id
            LEFT JOIN users     ON orders.assigned_user_id = users.id
            $whereClause
            ORDER BY $sortExpr $dir
        ";

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function markAsReturned(int $id): void
    {
        static::update($id, ['returned' => 1]);
    }
}
