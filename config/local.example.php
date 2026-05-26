<?php

/**
 * Lokale Konfiguration — enthält serverseitige Geheimnisse.
 *
 * Anleitung:
 *   Diese Datei als config/local.php kopieren und die Werte anpassen.
 *   config/local.php ist in .gitignore eingetragen und landet nie im Repo.
 *
 * Auf dem Server (FTP):
 *   1. Diese Datei als "local.php" in denselben Ordner hochladen
 *   2. SETUP_PASSWORD durch ein eigenes Passwort ersetzen
 */

// Passwort für den Browser-Zugang zu public/setup.php
define('SETUP_PASSWORD', 'hier-eigenes-passwort-eintragen');

// Wie viele Tage darf ein Mitarbeiter seine Tätigkeit noch bearbeiten?
// 0 = kein Zeitlimit. Admins und Koordinatoren sind davon nicht betroffen.
define('ACTIVITY_EDIT_DAYS', 14);
