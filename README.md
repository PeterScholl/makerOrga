# makerOrga

Verwaltungssoftware für die Make & Repair AG — eine schulische Werkstattgruppe, die Geräte repariert und Projekte umsetzt.

Gebaut mit **PHP** und **SQLite**, ohne externe Frameworks. Bewusst einfach gehalten, damit Schülerinnen und Schüler den Code verstehen und mitentwickeln können.

---

## Was kann die App?

- **Auftragsverwaltung** — Reparaturen und Projekte erfassen, zuweisen und nachverfolgen
- **Kundenverwaltung** — Personen verwalten, die Geräte abgegeben haben
- **Mitarbeiterverwaltung** — Schülerinnen und Schüler, Tätigkeiten und Stunden erfassen
- **Benutzerverwaltung** — Admin- und Koordinatorenrollen

---

## Projektstruktur

```
makerOrga/
├── config/          # Konfiguration (Datenbankpfad, Einstellungen)
├── db/
│   └── migrations/  # SQL-Dateien für Datenbankänderungen
├── src/
│   ├── models/      # Datenbankzugriff (je eine Klasse pro Tabelle)
│   └── controllers/ # Ablaufsteuerung (was passiert bei welcher Anfrage)
├── views/           # HTML-Templates (bekommen Daten, geben HTML zurück)
│   ├── layout/      # Wiederverwendbare Teile (Header, Footer)
│   ├── orders/
│   ├── customers/
│   └── users/
├── public/          # Einziger Ordner der vom Webserver erreichbar ist
│   ├── index.php    # Einstiegspunkt für alle Anfragen
│   └── assets/      # CSS, JavaScript
└── docs/            # Weiterführende Dokumentation
```

---

## Lokale Installation

### Voraussetzungen

- PHP 8.1 oder neuer (`php --version`)
- PHP-Extension `pdo_sqlite` (meist vorinstalliert)
- Git

### Setup

```bash
git clone <repo-url> makerOrga
cd makerOrga

# Datenbank initialisieren (führt alle Migrationen aus)
php db/migrate.php

# Lokalen Entwicklungsserver starten
php -S localhost:8000 -t public/
```

Dann im Browser: [http://localhost:8000](http://localhost:8000)

---

## Mitentwickeln

Lies zuerst [CONTRIBUTING.md](CONTRIBUTING.md) — dort steht wie wir mit Git arbeiten, wie Branches benannt werden und wie ein Pull Request aussehen soll.

---

## Datenbank & Migrationen

Wir nutzen **Migrationen**: Jede Änderung an der Datenbankstruktur bekommt eine eigene nummerierte SQL-Datei in `db/migrations/`. So kann jede Person im Team die Datenbank auf den aktuellen Stand bringen, ohne manuell etwas ändern zu müssen.

```bash
php db/migrate.php   # Führt alle noch nicht ausgeführten Migrationen aus
```

Die Datenbank-Datei selbst (`*.sqlite`) ist in `.gitignore` eingetragen und landet nie im Repo.
