<?php

/**
 * Seed-Skript — legt einen ersten Admin-Benutzer an.
 *
 * Aufruf: php db/seed.php
 *
 * Nur einmalig nötig, oder nach dem Löschen der Datenbank.
 * Bestehende Benutzer werden nicht überschrieben.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

$name     = 'Admin';
$username = 'admin';
$email    = 'admin@makerOrga.local';
$password = 'admin123';

$pdo = new PDO('sqlite:' . DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Prüfen ob schon ein Benutzer mit dieser E-Mail existiert
$existing = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$existing->execute([$email]);

if ($existing->fetch()) {
    echo "Benutzer '$email' existiert bereits — nichts geändert.\n";
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([
    $name,
    $username,
    $email,
    password_hash($password, PASSWORD_BCRYPT),
    'admin',
]);

echo "Admin-Benutzer angelegt:\n";
echo "  Benutzername: $username\n";
echo "  Passwort:     $password\n";
echo "\nBitte nach dem ersten Login das Passwort ändern!\n";
