# Mitentwickeln — wie wir zusammenarbeiten

Willkommen! Dieses Dokument erklärt, wie wir mit Git arbeiten und wie du eine Änderung einbringen kannst.

---

## Git-Grundprinzip

Wir arbeiten nie direkt auf dem `main`-Branch. `main` ist immer funktionstüchtig.

Jede Änderung — egal wie klein — bekommt einen eigenen **Feature-Branch**.

```
main
 └── feature/order-list      ← du arbeitest hier
 └── fix/migration-typo      ← jemand anderes arbeitet hier
```

---

## Einen neuen Branch anlegen

```bash
# Zuerst sicherstellen, dass main aktuell ist
git checkout main
git pull

# Neuen Branch erstellen und wechseln
git checkout -b feature/meine-neue-funktion
```

### Branch-Namen

| Typ | Muster | Beispiel |
|-----|--------|---------|
| Neue Funktion | `feature/...` | `feature/customer-form` |
| Fehlerbehebung | `fix/...` | `fix/order-status-update` |
| Dokumentation | `docs/...` | `docs/installation` |
| Datenbank | `db/...` | `db/add-priority-column` |

---

## Arbeiten & Commits

```bash
# Status anzeigen — was hat sich geändert?
git status

# Dateien zur nächsten Commit-Nachricht hinzufügen
git add src/models/Order.php

# Commit erstellen
git commit -m "Auftragsstatus-Filter in Order::findAll() hinzugefügt"
```

### Gute Commit-Nachrichten

- Kurz und konkret (max. 72 Zeichen)
- Beschreibt **was** geändert wurde, nicht warum du es gemacht hast
- Bevorzugt auf Deutsch alternativ Englisch — Hauptsache einheitlich im Branch

---

## Pull Request erstellen

Wenn deine Änderung fertig ist:

```bash
# Branch auf GitHub/Gitea hochladen
git push -u origin feature/meine-neue-funktion
```

Dann auf der Plattform einen **Pull Request** (PR) öffnen:

1. Titel: kurze Beschreibung der Änderung
2. Beschreibung: Was hast du gemacht? Warum? Gibt es etwas zu testen?
3. Jemanden als Reviewer eintragen (z.B. den Kursleiter)

Der PR wird dann angeschaut, kommentiert und schließlich in `main` gemergt.

---

## Neue Klasse angelegt? Autoloader aktualisieren

Wer eine neue PHP-Klasse in `src/` erstellt, muss danach einmal ausführen:

```bash
composer dump-autoload
```

Sonst findet der Editor (und zur Laufzeit PHP selbst) die Klasse nicht.  
Faustregel: neue Datei in `src/` → `composer dump-autoload`.

---

## Vor dem PR: kurz testen

```bash
# Autoloader aktualisieren (falls neue Klassen dazugekommen sind)
composer dump-autoload

# Migrationen laufen lassen (falls du DB-Änderungen hast)
php db/migrate.php

# Lokalen Server starten und kurz durchklicken
php -S localhost:8000 -t public/
```

---

## Fragen?

Einfach im PR als Kommentar stellen oder direkt ansprechen. Kein Code ist zu klein und keine Frage zu dumm.
