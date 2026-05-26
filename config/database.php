<?php

// Pfad zur SQLite-Datenbankdatei (liegt bewusst außerhalb von public/)
define('DB_PATH', __DIR__ . '/../db/makerOrga.sqlite');

// Wie viele Tage darf ein Mitarbeiter seine Tätigkeit noch bearbeiten?
// 0 = kein Zeitlimit. Kann in config/local.php überschrieben werden.
if (!defined('ACTIVITY_EDIT_DAYS')) {
    define('ACTIVITY_EDIT_DAYS', 7);
}
