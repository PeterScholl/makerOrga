-- Initiales Datenbankschema für makerOrga
-- Legt die vier Kerntabellen an: users, customers, orders, activities

-- Benutzer (Admins, Koordinatoren, Mitarbeiter/Schüler)
CREATE TABLE IF NOT EXISTS users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    email      TEXT    NOT NULL UNIQUE,
    password   TEXT    NOT NULL,               -- bcrypt-Hash, niemals Klartext
    role       TEXT    NOT NULL DEFAULT 'member'
                       CHECK(role IN ('admin', 'coordinator', 'member')),
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Kunden (Personen die Geräte abgegeben haben)
CREATE TABLE IF NOT EXISTS customers (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    email      TEXT,
    phone      TEXT,
    notes      TEXT,                           -- Freitextfeld für interne Notizen
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Aufträge (Reparaturen oder Projekte)
CREATE TABLE IF NOT EXISTS orders (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    type             TEXT    NOT NULL DEFAULT 'repair'
                             CHECK(type IN ('repair', 'project')),
    title            TEXT    NOT NULL,
    description      TEXT,                     -- Fehler- oder Aufgabenbeschreibung
    device_info      TEXT,                     -- Gerätebeschreibung (Marke, Modell, ...)
    status           TEXT    NOT NULL DEFAULT 'open'
                             CHECK(status IN ('open', 'in_progress', 'done', 'closed')),
    priority         TEXT    NOT NULL DEFAULT 'normal'
                             CHECK(priority IN ('low', 'normal', 'high')),
    customer_id      INTEGER REFERENCES customers(id) ON DELETE SET NULL,
    assigned_user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    received_at      TEXT    NOT NULL DEFAULT (datetime('now')),  -- Datum der Abgabe
    completed_at     TEXT,                     -- Datum des Abschlusses (NULL = noch offen)
    result           TEXT,                     -- Ergebnis/Abschlussbericht
    returned         INTEGER NOT NULL DEFAULT 0  -- 0 = nicht zurückgegeben, 1 = zurückgegeben
                             CHECK(returned IN (0, 1)),
    created_at       TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at       TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Tätigkeiten (was hat wer wann an einem Auftrag gemacht)
CREATE TABLE IF NOT EXISTS activities (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id    INTEGER REFERENCES orders(id) ON DELETE CASCADE,  -- kann NULL sein (auftragsunabhängig)
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    description TEXT    NOT NULL,
    worked_at   TEXT    NOT NULL,              -- Datum/Uhrzeit der Tätigkeit (editierbar)
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);
