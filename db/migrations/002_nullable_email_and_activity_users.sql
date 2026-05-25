-- Migration 002: E-Mail bei Benutzern optional machen, Tätigkeiten können
-- mehreren Mitarbeitern zugewiesen werden (Viele-zu-Viele)

-- ── Schritt 1: users-Tabelle neu bauen mit optionaler E-Mail ─────────────────
CREATE TABLE users_new (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    email      TEXT    UNIQUE,                 -- kein NOT NULL mehr
    password   TEXT    NOT NULL,
    role       TEXT    NOT NULL DEFAULT 'member'
                       CHECK(role IN ('admin', 'coordinator', 'member')),
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT    NOT NULL DEFAULT (datetime('now'))
);
INSERT INTO users_new SELECT * FROM users;
DROP TABLE users;
ALTER TABLE users_new RENAME TO users;

-- ── Schritt 2: Zwischentabelle für Mitarbeiter ↔ Tätigkeiten ────────────────
CREATE TABLE activity_users (
    activity_id INTEGER NOT NULL REFERENCES activities(id) ON DELETE CASCADE,
    user_id     INTEGER NOT NULL REFERENCES users(id)     ON DELETE CASCADE,
    PRIMARY KEY (activity_id, user_id)
);

-- Bestehende Einzel-Zuordnungen übernehmen
INSERT INTO activity_users (activity_id, user_id)
SELECT id, user_id FROM activities WHERE user_id IS NOT NULL;

-- ── Schritt 3: activities-Tabelle neu bauen ohne user_id ────────────────────
CREATE TABLE activities_new (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id    INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    description TEXT    NOT NULL,
    worked_at   TEXT    NOT NULL,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);
INSERT INTO activities_new
    SELECT id, order_id, description, worked_at, created_at, updated_at
    FROM activities;
DROP TABLE activities;
ALTER TABLE activities_new RENAME TO activities;
