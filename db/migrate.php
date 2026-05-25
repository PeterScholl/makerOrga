<?php

/**
 * Migrationsskript — führt alle noch nicht ausgeführten SQL-Dateien aus.
 *
 * Aufruf: php db/migrate.php
 *
 * Wie es funktioniert:
 *   1. Alle Dateien in db/migrations/ werden alphabetisch eingelesen (001_..., 002_..., ...)
 *   2. Eine interne Tabelle "migrations" merkt sich, welche bereits ausgeführt wurden
 *   3. Nur neue Dateien werden ausgeführt — bereits laufende bleiben unberührt
 */

require_once __DIR__ . '/../config/database.php';

$pdo = new PDO('sqlite:' . DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Migrations-Tabelle anlegen falls sie noch nicht existiert
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id        INTEGER PRIMARY KEY AUTOINCREMENT,
        filename  TEXT NOT NULL UNIQUE,
        run_at    TEXT NOT NULL DEFAULT (datetime('now'))
    )
");

// Alle bereits ausgeführten Migrationen einlesen
$done = $pdo->query("SELECT filename FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// Alle SQL-Dateien im Migrations-Ordner alphabetisch sortiert einlesen
$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);

$ran = 0;

foreach ($files as $file) {
    $filename = basename($file);

    // Bereits ausgeführte Dateien überspringen
    if (in_array($filename, $done)) {
        echo "  übersprungen: $filename\n";
        continue;
    }

    $sql = file_get_contents($file);

    // Gesamte Migration in einer Transaktion ausführen — alles oder nichts
    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);
        $pdo->commit();
        echo "  ausgeführt:   $filename\n";
        $ran++;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "  FEHLER in $filename: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($ran === 0) {
    echo "Datenbank ist bereits aktuell.\n";
} else {
    echo "$ran Migration(en) erfolgreich ausgeführt.\n";
}
