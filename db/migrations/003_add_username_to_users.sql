-- Migration 003: Benutzername als Login-Identifier hinzufügen.
-- Der Benutzername ersetzt die E-Mail beim Login, da E-Mail optional ist.

ALTER TABLE users ADD COLUMN username TEXT;

-- Bestehende Benutzer bekommen einen temporären Benutzernamen aus dem Namen
UPDATE users SET username = lower(replace(name, ' ', '.')) WHERE username IS NULL;

-- Eindeutigkeit sicherstellen (als separater Schritt, da SQLite kein
-- ADD COLUMN ... UNIQUE unterstützt)
CREATE UNIQUE INDEX users_username_unique ON users(username);
