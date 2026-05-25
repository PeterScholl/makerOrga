<?php

/**
 * Singleton-Klasse für die Datenbankverbindung.
 *
 * Warum Singleton? Eine PDO-Verbindung aufzubauen kostet Zeit.
 * Dieses Muster stellt sicher, dass pro Request genau eine
 * Verbindung existiert, die von allen Models geteilt wird.
 *
 * Verwendung:
 *   $pdo = Database::connection();
 */
class Database
{
    private static ?PDO $instance = null;

    // Konstruktor privat — niemand soll "new Database()" aufrufen können
    private function __construct() {}

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            require_once __DIR__ . '/../config/database.php';

            self::$instance = new PDO('sqlite:' . DB_PATH);

            // Bei Fehler eine Exception werfen statt still zu scheitern
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Ergebnisse als assoziative Arrays zurückgeben (z.B. $row['name'])
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Fremdschlüssel in SQLite müssen explizit aktiviert werden
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }

        return self::$instance;
    }
}
