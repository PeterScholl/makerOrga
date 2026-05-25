# Berechtigungskonzept

Wer darf was? Diese Datei ist eine Nachschlagehilfe — das Konzept dahinter steht in [docs/konzepte.md](konzepte.md#autorisierung-und-rollen).

---

## Rollen

| Rolle | Beschreibung |
|-------|-------------|
| `admin` | Vollzugriff auf alles. Verwaltet Benutzer und Kontaktdaten von Kunden. |
| `coordinator` | Kann Aufträge anlegen und vollständig bearbeiten (inkl. Zuweisung). Kann Kunden anlegen und deren Namen/Notizen bearbeiten. Kann keine Benutzer verwalten. |
| `member` | Kann eigene zugewiesene Aufträge bearbeiten (Status, Beschreibung, Ergebnis). Kann keine Aufträge anlegen oder Zuweisungen ändern. |

---

## Aufträge

| Aktion | admin | coordinator | member |
|--------|:-----:|:-----------:|:------:|
| Liste anzeigen | ✓ | ✓ | ✓ |
| Einzelauftrag anzeigen | ✓ | ✓ | ✓ |
| Neuen Auftrag anlegen | ✓ | ✓ | — |
| Auftrag bearbeiten (zugewiesen) | ✓ | ✓ | ✓ |
| Auftrag bearbeiten (nicht zugewiesen) | ✓ | ✓ | — |
| Kunden-Zuweisung ändern | ✓ | ✓ | — |
| Mitarbeiter-Zuweisung ändern | ✓ | ✓ | — |
| Auftrag löschen | ✓ | — | — |

**Felder die ein Mitarbeiter bei einem eigenen Auftrag bearbeiten darf:**  
Typ, Titel, Gerätebeschreibung, Fehlerbeschreibung, Priorität, Abgabedatum, Status, Abschlussdatum, Ergebnis, Rückgabe-Checkbox.

**Felder die nur Koordinator oder Admin ändern dürfen:**  
Kundenzuweisung, Mitarbeiterzuweisung.

---

## Kunden

| Aktion | admin | coordinator | member |
|--------|:-----:|:-----------:|:------:|
| Liste anzeigen | ✓ | ✓ | ✓ |
| Kunden anzeigen | ✓ | ✓ | ✓ |
| Neuen Kunden anlegen | ✓ | ✓ | — |
| Name und Notizen bearbeiten | ✓ | ✓ | — |
| E-Mail bearbeiten | ✓ | — | — |
| Telefon bearbeiten | ✓ | — | — |

**Hintergrund:** E-Mail und Telefon sind Kontaktdaten die beim Benachrichtigen von Kunden verwendet werden. Damit diese nicht versehentlich geändert werden, sind sie dem Admin vorbehalten.

---

## Mitarbeiter (Benutzer)

| Aktion | admin | coordinator | member |
|--------|:-----:|:-----------:|:------:|
| Liste anzeigen | ✓ | ✓ | ✓ |
| Profil anzeigen | ✓ | ✓ | ✓ |
| Neuen Mitarbeiter anlegen | ✓ | — | — |
| Mitarbeiter bearbeiten | ✓ | — | — |

---

## Tätigkeiten

Tätigkeiten sind Einträge zu einem Auftrag oder unabhängig davon.  
_(Berechtigungen werden ergänzt sobald die Tätigkeitsverwaltung ausgebaut ist.)_

---

## Wo ist der Schutz im Code?

Die Zugriffsregeln sitzen in den Controllern, nicht in den Views. Views blenden Buttons aus (Komfort) — aber die echte Prüfung findet serverseitig statt:

| Datei | Geschützte Methoden |
|-------|-------------------|
| `src/controllers/UserController.php` | `create`, `store`, `edit`, `update` → nur `admin` |
| `src/controllers/OrderController.php` | `create`, `store` → `admin`/`coordinator`; `edit`, `update` → `canEditOrder()`; `destroy` → nur `admin` |
| `src/controllers/CustomerController.php` | `create`, `store`, `edit`, `update` → `admin`/`coordinator`; E-Mail/Telefon in `store`/`update` → nur `admin` |
