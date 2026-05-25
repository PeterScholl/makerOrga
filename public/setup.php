<?php

/**
 * Browser-Setup: führt Migrationen aus und legt den ersten Admin an.
 *
 * Zugang: eingeloggt als Admin  ODER  Setup-Passwort aus config/local.php
 *
 * Deployment-Anleitung:
 *   1. config/local.example.php → config/local.php kopieren und Passwort setzen
 *   2. Alle Dateien inkl. vendor/ per FTP hochladen
 *   3. https://deinedomain.de/setup.php im Browser aufrufen
 */

session_start();

$root = dirname(__DIR__);

// Lokale Konfiguration einbinden (enthält SETUP_PASSWORD)
if (file_exists($root . '/config/local.php')) {
    require_once $root . '/config/local.php';
}
require_once $root . '/config/database.php';

// --- Zugriffsschutz ---

$isAdminSession = ($_SESSION['user_role'] ?? '') === 'admin';
$isSetupAuthed  = $_SESSION['setup_authed'] ?? false;
$loginError     = null;

if (!$isAdminSession && !$isSetupAuthed) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_password'])) {
        if (!defined('SETUP_PASSWORD')) {
            $loginError = 'config/local.php fehlt oder enthält kein SETUP_PASSWORD. Bitte config/local.example.php kopieren und anpassen.';
        } elseif ($_POST['setup_password'] === SETUP_PASSWORD) {
            $_SESSION['setup_authed'] = true;
            header('Location: /setup.php');
            exit;
        } else {
            $loginError = 'Falsches Passwort.';
        }
    }

    // Kein Zugang → Passwort-Formular anzeigen
    renderPasswordForm($loginError);
    exit;
}

// --- Ab hier: Zugang gewährt ---

$steps    = [];
$hasError = false;

// Schritt 1: Voraussetzungen prüfen
if (!is_writable($root . '/db')) {
    $steps[]  = ['error', 'Ordner db/ ist nicht beschreibbar — Schreibrechte per FTP setzen (chmod 775)'];
    $hasError = true;
}
if (!file_exists($root . '/vendor/autoload.php')) {
    $steps[]  = ['error', 'vendor/autoload.php fehlt — lokal "composer install --no-dev" ausführen und vendor/ mit hochladen'];
    $hasError = true;
}

// Schritt 2: Migrationen ausführen
if (!$hasError) {
    require_once $root . '/vendor/autoload.php';

    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                filename  TEXT NOT NULL UNIQUE,
                run_at    TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        $done  = $pdo->query("SELECT filename FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        $files = glob($root . '/db/migrations/*.sql');
        sort($files);

        $ran = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $done, true)) {
                $steps[] = ['skip', "Migration bereits ausgeführt: $filename"];
                continue;
            }
            $pdo->beginTransaction();
            try {
                $pdo->exec(file_get_contents($file));
                $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)")->execute([$filename]);
                $pdo->commit();
                $steps[] = ['ok', "Migration ausgeführt: $filename"];
                $ran++;
            } catch (Exception $e) {
                $pdo->rollBack();
                $steps[] = ['error', "Fehler in $filename: " . $e->getMessage()];
                $hasError = true;
            }
        }

        if ($ran === 0 && !$hasError) {
            $steps[] = ['skip', 'Datenbank bereits aktuell — keine neuen Migrationen'];
        }

    } catch (Exception $e) {
        $steps[]  = ['error', 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()];
        $hasError = true;
    }
}

// Schritt 3: Admin-Benutzer anlegen (nur wenn noch keine Benutzer existieren)
$seeded = false;
if (!$hasError) {
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count === 0) {
            $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)")
                ->execute(['Admin', 'admin', 'admin@makerOrga.local', password_hash('admin123', PASSWORD_BCRYPT), 'admin']);
            $steps[] = ['ok', 'Admin-Benutzer angelegt'];
            $seeded  = true;
        } else {
            $steps[] = ['skip', "Benutzer bereits vorhanden ($count) — kein Seed nötig"];
        }
    } catch (Exception $e) {
        $steps[]  = ['error', 'Benutzer anlegen fehlgeschlagen: ' . $e->getMessage()];
        $hasError = true;
    }
}

renderPage($steps, $hasError, $seeded, $isAdminSession);

// -------------------------------------------------------------------------

function renderPasswordForm(?string $error): void
{
    $noConfig = !defined('SETUP_PASSWORD') && $error === null;
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MakerOrga Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container" style="max-width: 400px; margin-top: 80px">
        <h1 class="h4 mb-4">MakerOrga — Setup</h1>

        <?php if ($noConfig): ?>
        <div class="alert alert-warning">
            <strong>config/local.php fehlt.</strong><br>
            Bitte <code>config/local.example.php</code> als <code>config/local.php</code> kopieren
            und ein <code>SETUP_PASSWORD</code> setzen.
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Setup-Passwort eingeben <em>oder</em>
                    <a href="/login">als Admin einloggen</a>.
                </p>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif ?>
                <form method="post">
                    <div class="mb-3">
                        <input type="password" name="setup_password" class="form-control"
                               placeholder="Setup-Passwort" autofocus required>
                    </div>
                    <button class="btn btn-primary w-100">Weiter</button>
                </form>
            </div>
        </div>
        <?php endif ?>
    </div>
    </body>
    </html>
    <?php
}

function renderPage(array $steps, bool $hasError, bool $seeded, bool $isAdminSession): void
{
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MakerOrga Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container" style="max-width: 560px; margin-top: 60px">

        <h1 class="h3 mb-1">MakerOrga</h1>
        <p class="text-muted mb-4">
            Einrichtung
            <?= $isAdminSession ? '<span class="badge bg-success ms-1">eingeloggt als Admin</span>' : '' ?>
        </p>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <?php foreach ($steps as [$type, $msg]): ?>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <?php if ($type === 'ok'): ?>
                        <span class="text-success fw-bold">✓</span>
                        <span><?= htmlspecialchars($msg) ?></span>
                    <?php elseif ($type === 'error'): ?>
                        <span class="text-danger fw-bold">✗</span>
                        <span class="text-danger"><?= htmlspecialchars($msg) ?></span>
                    <?php else: ?>
                        <span class="text-muted">–</span>
                        <span class="text-muted"><?= htmlspecialchars($msg) ?></span>
                    <?php endif ?>
                </div>
                <?php endforeach ?>
            </div>
        </div>

        <?php if ($hasError): ?>

            <div class="alert alert-danger">
                <strong>Setup fehlgeschlagen.</strong><br>
                Bitte die Fehler oben beheben und die Seite neu laden.
            </div>

        <?php else: ?>

            <?php if ($seeded): ?>
            <div class="alert alert-info">
                <strong>Zugangsdaten für den ersten Login:</strong><br>
                Benutzername: <code>admin</code><br>
                Passwort: <code>admin123</code><br>
                <small class="text-muted">Bitte nach dem ersten Login unter Mitarbeiter ändern.</small>
            </div>
            <?php endif ?>

            <div class="alert alert-success">
                <strong>Setup abgeschlossen.</strong> Die App ist einsatzbereit.
            </div>

            <a href="/" class="btn btn-primary">Zur App &rarr;</a>

        <?php endif ?>

    </div>
    </body>
    </html>
    <?php
}
