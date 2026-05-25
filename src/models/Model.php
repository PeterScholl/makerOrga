<?php

require_once __DIR__ . '/../Database.php';

/**
 * Basisklasse für alle Models.
 *
 * Jedes Model (Order, Customer, User, Activity) erbt von hier.
 * Gemeinsame Aufgaben werden einmal hier gelöst, nicht in jeder Klasse neu.
 */
abstract class Model
{
    // Name der Datenbanktabelle — wird in jeder Unterklasse überschrieben
    protected static string $table = '';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    /**
     * Alle Datensätze der Tabelle zurückgeben.
     * Optional: assoziatives Array mit WHERE-Bedingungen übergeben.
     *
     * Beispiel: Order::findAll(['status' => 'open'])
     */
    public static function findAll(array $conditions = []): array
    {
        $sql = 'SELECT * FROM ' . static::$table;

        if (!empty($conditions)) {
            $clauses = array_map(fn($col) => "$col = ?", array_keys($conditions));
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    /**
     * Einen einzelnen Datensatz anhand seiner ID zurückgeben.
     * Gibt null zurück wenn kein Datensatz gefunden wurde.
     */
    public static function findById(int $id): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM ' . static::$table . ' WHERE id = ?'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Einen neuen Datensatz anlegen.
     * Gibt die ID des neuen Eintrags zurück.
     */
    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = static::db()->prepare(
            "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)"
        );
        $stmt->execute(array_values($data));
        return (int) static::db()->lastInsertId();
    }

    /**
     * Einen bestehenden Datensatz aktualisieren.
     * updated_at wird automatisch auf den aktuellen Zeitpunkt gesetzt.
     */
    public static function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $clauses = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));

        $stmt = static::db()->prepare(
            "UPDATE " . static::$table . " SET $clauses WHERE id = ?"
        );
        $stmt->execute([...array_values($data), $id]);
    }

    /**
     * Einen Datensatz anhand seiner ID löschen.
     */
    public static function delete(int $id): void
    {
        $stmt = static::db()->prepare(
            'DELETE FROM ' . static::$table . ' WHERE id = ?'
        );
        $stmt->execute([$id]);
    }
}
