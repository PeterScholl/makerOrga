# makerOrga

Verwaltungssoftware für die Make & Repair AG — eine schulische Werkstattgruppe, die Geräte repariert und Projekte umsetzt.

Gebaut mit **PHP** und **SQLite**, ohne externe Frameworks. Bewusst einfach gehalten, damit Schülerinnen und Schüler den Code verstehen und mitentwickeln können.

---

## Was kann die App?

- **Auftragsverwaltung** — Reparaturen und Projekte erfassen, zuweisen und nachverfolgen
- **Kundenverwaltung** — Personen verwalten, die Geräte abgegeben haben
- **Mitarbeiterverwaltung** — Schülerinnen und Schüler, Tätigkeiten und Stunden erfassen
- **Benutzerverwaltung** — Admin- und Koordinatorenrollen mit abgestuften Berechtigungen

---

## Projektstruktur

```text
makerOrga/
├── config/
│   ├── database.php         # Datenbankpfad (in git)
│   ├── local.php            # Lokale Geheimnisse, z.B. SETUP_PASSWORD (nicht in git)
│   └── local.example.php   # Vorlage für local.php (in git)
├── db/
│   ├── migrations/          # Nummerierte SQL-Dateien für Schemaänderungen
│   ├── migrate.php          # Migrationsskript (CLI)
│   └── seed.php             # Legt ersten Admin-Benutzer an (CLI)
├── src/
│   ├── models/              # Datenbankzugriff (je eine Klasse pro Tabelle)
│   └── controllers/         # Ablaufsteuerung (was passiert bei welcher Anfrage)
├── views/
│   ├── layout/              # Wiederverwendbare Teile (Header, Footer)
│   ├── orders/
│   ├── customers/
│   └── users/
├── public/                  # Einziger Ordner der vom Webserver erreichbar ist
│   ├── index.php            # Einstiegspunkt für alle Anfragen
│   ├── setup.php            # Browser-Setup (Migrationen + Seed, passwortgeschützt)
│   └── assets/              # CSS, JavaScript
└── docs/
    ├── konzepte.md          # Erklärungen zu MVC, PDO, Migrationen usw.
    └── berechtigungen.md    # Wer darf was — Rollen und Zugriffsregeln
```

---

## Lokale Installation

### Voraussetzungen

- PHP 8.1 oder neuer (`php --version`)
- PHP-Extension `pdo_sqlite` (meist vorinstalliert)
- Composer (`composer --version`)
- Git

### Setup

```bash
git clone <repo-url> makerOrga
cd makerOrga

# Autoloader erzeugen (einmalig nach dem Klonen)
# → was Composer ist: docs/konzepte.md
composer install

# Datenbank initialisieren (führt alle Migrationen aus)
php db/migrate.php

# Ersten Admin-Benutzer anlegen
php db/seed.php

# Lokalen Entwicklungsserver starten
php -S localhost:8000 -t public/
```

Erster Login unter [http://localhost:8000](http://localhost:8000):
Benutzername `admin` / Passwort `admin123` — bitte nach dem ersten Login ändern.

> `config/local.php` wird lokal nicht benötigt. Das Setup-Skript
> (`public/setup.php`) ist nur für den Serverbetrieb ohne SSH gedacht.

---

## Deployment auf einem Webserver (nur FTP-Zugang)

### Voraussetzungen auf dem Server

- PHP 8.1+ mit Extension `pdo_sqlite`
- Apache mit aktivem `mod_rewrite` (für die `.htaccess`)
- Subdomain zeigt auf den Ordner `public/` — nicht auf den Projektwurzel

### Einmalig vorbereiten (lokal)

```bash
# Autoloader ohne Entwicklungspakete erzeugen
composer install --no-dev
```

### Hochladen per FTP

Alle Dateien und Ordner hochladen — **inklusive `vendor/`**, **außer**:

- `.git/`
- `db/*.sqlite` (wird auf dem Server neu angelegt)
- `config/local.php` (wird separat angelegt, siehe nächster Schritt)

### config/local.php auf dem Server anlegen

`config/local.example.php` als Vorlage nehmen, als `config/local.php` hochladen
und ein eigenes Passwort eintragen:

```php
define('SETUP_PASSWORD', 'hier-eigenes-passwort-eintragen');
```

### Einrichtung im Browser

```text
https://deinedomain.de/setup.php
```

Das Skript fragt nach dem Setup-Passwort — oder lässt einen eingeloggten Admin direkt durch.
Es führt alle ausstehenden Migrationen aus und legt den ersten Admin-Benutzer an, falls noch keiner existiert.

`setup.php` kann dauerhaft auf dem Server bleiben, da es durch das Passwort geschützt ist.

### Updates einspielen

```text
1. Geänderte Dateien per FTP hochladen
2. Falls neue Migrationen vorhanden: setup.php im Browser aufrufen
```

---

## Mitentwickeln

Lies zuerst [CONTRIBUTING.md](CONTRIBUTING.md) — dort steht wie wir mit Git arbeiten,
wie Branches benannt werden und wie ein Pull Request aussehen soll.

Begriffe wie PDO, MVC oder Migrationen werden in [docs/konzepte.md](docs/konzepte.md) erklärt.
Das Berechtigungskonzept (wer darf was) steht in [docs/berechtigungen.md](docs/berechtigungen.md).

---

## Datenbank & Migrationen

Jede Änderung an der Datenbankstruktur bekommt eine eigene nummerierte SQL-Datei in `db/migrations/`.
So kann jede Person im Team die Datenbank mit einem Befehl auf den aktuellen Stand bringen:

```bash
php db/migrate.php
```

Auf dem Server (ohne SSH) übernimmt das `public/setup.php` im Browser.

Die Datenbankdatei selbst (`*.sqlite`) ist in `.gitignore` eingetragen und landet nie im Repo.
