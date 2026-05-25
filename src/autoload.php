<?php

/**
 * Einfacher Autoloader — lädt Klassen automatisch wenn sie gebraucht werden.
 *
 * Konvention: Klassenname = Dateiname.
 *   Order     → src/models/Order.php
 *   Database  → src/Database.php
 *
 * So braucht keine Datei mehr manuell require_once aufrufen.
 * Der Autoloader wird einmal in public/index.php eingebunden.
 */
spl_autoload_register(function (string $className): void {
    $locations = [
        __DIR__ . '/' . $className . '.php',
        __DIR__ . '/models/' . $className . '.php',
        __DIR__ . '/controllers/' . $className . '.php',
    ];

    foreach ($locations as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
