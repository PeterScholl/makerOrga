# Berechtigungskonzept

Wer darf was? Diese Datei ist eine Nachschlagehilfe — das Konzept dahinter steht in [docs/konzepte.md](konzepte.md#autorisierung-und-rollen).

---

## Rollen

| Rolle | Beschreibung |
|-------|-------------|
| `admin` | Vollzugriff auf alles. Verwaltet Benutzer und Kontaktdaten von Kunden. |
| `coordinator` | Kann Aufträge anlegen und vollständig bearbeiten (inkl. Zuweisung). Kann Kunden anlegen und deren Namen/Notizen bearbeiten. Kann keine Benutzer verwalten. |
| `member` | Kann zugewiesene Aufträge bearbeiten (Status, Beschreibung, Ergebnis). Kann Tätigkeiten zu **beliebigen** Aufträgen eintragen — nicht nur zu eigenen. Kann eigene Tätigkeiten innerhalb des Zeitfensters bearbeiten und löschen. Kann keine Aufträge anlegen oder Zuweisungen ändern. |

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
| E-Mail bearbeiten | ✓ | ✓ | — |
| Telefon bearbeiten | ✓ | ✓ | — |

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

Tätigkeiten sind Arbeitseinträge zu einem Auftrag (oder auftragsunabhängig).

| Aktion | admin | coordinator | member |
| ------ | :---: | :---------: | :----: |
| Tätigkeit eintragen (zu beliebigem Auftrag) | ✓ | ✓ | ✓ |
| Eigene Tätigkeit bearbeiten (im Zeitfenster) | ✓ | ✓ | ✓ |
| Beliebige Tätigkeit bearbeiten | ✓ | ✓ | — |
| Eigene Tätigkeit löschen (im Zeitfenster) | ✓ | ✓ | ✓ |
| Beliebige Tätigkeit löschen | ✓ | ✓ | — |
| Tätigkeitsübersicht aller Mitarbeiter | ✓ | — | — |

**Wichtig:** Die Zuweisung eines Auftrags schränkt nur das *Bearbeiten des Auftrags selbst* ein — nicht das Eintragen von Tätigkeiten. Ein Mitarbeiter der an einem Auftrag mitarbeitet aber nicht als Hauptverantwortlicher eingetragen ist, kann trotzdem Tätigkeitseinträge dazu erstellen.

**Zeitfenster:** Wie lange ein Mitarbeiter eigene Einträge nachbearbeiten kann, ist in `config/local.php` über `ACTIVITY_EDIT_DAYS` konfigurierbar (Standard: 14 Tage). Admin und Koordinator haben kein Zeitlimit.

---

## Wo ist der Schutz im Code?

Die Zugriffsregeln sitzen in den Controllern, nicht in den Views. Views blenden Buttons aus (Komfort) — aber die echte Prüfung findet serverseitig statt:

| Datei | Geschützte Methoden |
|-------|-------------------|
| `src/controllers/UserController.php` | `create`, `store`, `edit`, `update` → nur `admin` |
| `src/controllers/OrderController.php` | `create`, `store` → `admin`/`coordinator`; `edit`, `update` → `canEditOrder()`; `destroy` → nur `admin` |
| `src/controllers/CustomerController.php` | `create`, `store`, `edit`, `update` → `admin`/`coordinator`; E-Mail/Telefon in `store`/`update` → nur `admin` |
| `src/controllers/ActivityController.php` | `edit`, `update`, `destroy` → `Activity::isEditable()` |

### Wie weiß der Controller wer gerade angemeldet ist?

Beim Login speichert der Server ID und Rolle des Benutzers in der **Session**:

```php
$_SESSION['user_id']   = $user['id'];    // z.B. 5
$_SESSION['user_role'] = $user['role'];  // z.B. 'member'
```

Bei jeder weiteren Anfrage schickt der Browser seinen Session-Cookie mit — der Server liest daraus `$_SESSION` aus und weiß sofort, wer die Anfrage stellt. **Kein Datenbankzugriff nötig**, um die Identität des Benutzers festzustellen. Die Basisklasse `Controller` macht beides als Hilfsmethoden verfügbar:

```php
$this->currentUserId();   // liest $_SESSION['user_id']
$this->currentRole();     // liest $_SESSION['user_role']
```

### Beispiel: Mitarbeiter will einen Tätigkeitseintrag bearbeiten

Ein Mitarbeiter klickt auf "Bearbeiten" bei Tätigkeit Nr. 7. Das schickt eine Anfrage an `POST /activities/7`.

**1. Router** erkennt das Muster `/activities/{id}` und ruft `ActivityController::update("7")` auf.

**2. Controller** stellt zunächst fest, wer die Anfrage stellt — aus der Session, ohne Datenbankabfrage:

```php
$this->currentUserId();  // → 5
$this->currentRole();    // → 'member'
```

**3. Controller** lädt die Tätigkeit aus der Datenbank — er braucht die Daten um zu prüfen, ob der Zugriff erlaubt ist:

```php
$activity = Activity::findById(7);
// → ['id' => 7, 'created_at' => '2025-05-01 14:30', 'order_id' => 42, ...]
```

**4. Controller** prüft die Berechtigung mit `Activity::isEditable()`:

```php
Activity::isEditable($activity, $this->currentUserId(), $this->currentRole())
```

Intern passiert dabei folgendes:

- Rolle `admin` oder `coordinator`? → sofort erlaubt
- Sonst: War der Mitarbeiter an dieser Tätigkeit beteiligt? → Abfrage in `activity_users`-Tabelle
- Und: Liegt der Eintrag noch innerhalb des konfigurierten Zeitfensters (`ACTIVITY_EDIT_DAYS`)?

**5a. Zugriff verweigert** → `$this->forbidden()` → HTTP 403, Fehlermeldung, Ausführung endet.

**5b. Zugriff erlaubt** → Tätigkeit wird aktualisiert (Model → Datenbank) → Weiterleitung zum Auftrag.

Der Rest des Ablaufs — Model, Datenbank, View — funktioniert genauso wie in [docs/konzepte.md](konzepte.md#mvc--model-view-controller) beschrieben.
