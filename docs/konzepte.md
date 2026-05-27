# Konzepte & Begriffe

Kurze Erklärungen zu Technologien und Mustern die in diesem Projekt vorkommen.  
Kein Anspruch auf Vollständigkeit — nur das Wichtigste zum Loslegen.

---

## Router

Ein Router entscheidet welcher Code ausgeführt wird, abhängig davon welche URL aufgerufen wurde.

**Das Problem ohne Router:**  
Ohne Router bräuchte jede Seite eine eigene PHP-Datei: `orders.php`, `customers.php`, `orders_edit.php` usw. Diese Dateien wären direkt über den Browser erreichbar, die Ordnerstruktur wäre eng an die URLs gekoppelt, und es gäbe keinen zentralen Ort um z.B. Login-Prüfungen für alle Seiten einzubauen.

**Die Lösung:**  
Es gibt nur einen einzigen Einstiegspunkt: `public/index.php`. Die Datei `public/.htaccess` sorgt dafür, dass *alle* Anfragen dort ankommen — egal welche URL aufgerufen wurde:

```text
Browser ruft /orders auf
    → .htaccess leitet weiter an public/index.php
        → Router liest die URL aus
        → findet die passende Route: GET /orders → OrderController::index()
        → ruft die Methode auf
```

**Routen** sind die Verbindung zwischen einer URL+Methode und einer Controller-Methode:

```php
$router->get('/orders',        [OrderController::class, 'index']);   // Liste
$router->get('/orders/{id}',   [OrderController::class, 'show']);    // Einzelansicht
$router->post('/orders',       [OrderController::class, 'store']);   // Formular speichern
```

**URL-Parameter** wie `{id}` werden aus der URL extrahiert und an die Controller-Methode übergeben:

```text
GET /orders/42  →  OrderController::show(42)
```

**Schritt für Schritt am Beispiel `/orders/42`:**

```text
1. Browser         → sendet GET-Anfrage an /orders/42
2. .htaccess       → erkennt: keine echte Datei → leitet weiter an public/index.php
3. public/index.php → lädt den Autoloader, registriert alle Routen, ruft dispatch() auf
4. Router::dispatch() → vergleicht GET /orders/42 mit jeder registrierten Route
5. match()         → Route /orders/{id} passt → extrahiert "42" als Parameter
6. OrderController::show(42) wird aufgerufen
7. show()          → fragt das Model: Order::findById(42)
8. Model           → holt den Datensatz aus der SQLite-Datenbank
9. View            → bekommt die Daten und gibt HTML an den Browser zurück
```

---

## Composer

Composer ist ein Werkzeug das fremden Code für das eigene Projekt verwaltet.

**Das Problem ohne Composer:**  
Stell dir vor, du baust eine App und brauchst eine fertige Lösung für z.B. das Versenden von E-Mails. Du könntest den Code manuell herunterladen, in deinen Projektordner kopieren — und hoffen, dass du nicht vergisst ihn zu aktualisieren wenn es eine neue Version gibt. Bei zehn solchen Abhängigkeiten wird das schnell unübersichtlich.

**Die Lösung mit Composer:**  
In der Datei `composer.json` steht was das Projekt braucht. Composer lädt alles automatisch herunter und legt es im Ordner `vendor/` ab. Dieser Ordner wird *nicht* ins Git-Repo eingecheckt — er ist groß, und jede Person im Team kann ihn mit einem einzigen Befehl selbst erzeugen:

```bash
composer install
```

Das ist vergleichbar mit `npm install` in JavaScript-Projekten.

**Was `vendor/` enthält:**  
Alle heruntergeladenen Pakete — und eine wichtige Datei: `vendor/autoload.php`. Diese eine Datei lädt man am Anfang des Programms ein, und danach sind alle Klassen aus allen Paketen automatisch verfügbar. In diesem Projekt nutzen wir das auch für unsere eigenen Klassen — wie das funktioniert zeigt der nächste Abschnitt.

**Was `composer.json` in diesem Projekt macht:**  
Aktuell haben wir keine externen Pakete. Wir nutzen Composer nur damit die Entwicklungsumgebung (der Code-Editor) unsere eigenen Klassen findet und keine falschen Fehlermeldungen anzeigt:

```json
"autoload": {
    "classmap": ["src/"]
}
```

`classmap` bedeutet: "Schau in den Ordner `src/` und merke dir alle Klassen die du dort findest." So weiß der Editor dass `Order`, `Customer` usw. existieren — ohne dass wir in jeder Datei manuell `require_once` schreiben müssen.

---

## PDO — PHP Data Objects

PDO ist eine PHP-Erweiterung die den Zugriff auf Datenbanken vereinheitlicht.

**Das Problem ohne PDO:**  
Jede Datenbank hat ihre eigenen PHP-Funktionen. MySQL nutzt `mysqli_query()`, SQLite nutzt `sqlite_query()` usw. Wechselt man die Datenbank, muss man den gesamten Datenbankcode umschreiben.

**Die Lösung mit PDO:**  
PDO stellt eine einheitliche Schnittstelle bereit. Der Code bleibt gleich — nur die Verbindungszeile ändert sich:

```php
// SQLite
$pdo = new PDO('sqlite:/pfad/zur/datenbank.sqlite');

// MySQL (nur diese Zeile wäre anders)
$pdo = new PDO('mysql:host=localhost;dbname=makerOrga', 'user', 'passwort');
```

**Prepared Statements:**  
PDO schützt außerdem vor SQL-Injection — einem der häufigsten Sicherheitsprobleme in Webanwendungen. Statt Benutzereingaben direkt in SQL einzubauen, verwendet man Platzhalter:

```php
// Unsicher — niemals so machen:
$pdo->query("SELECT * FROM users WHERE email = '$email'");

// Sicher mit PDO:
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

---

## MVC — Model, View, Controller

Ein Architekturmuster das den Code in drei klar getrennte Verantwortlichkeiten aufteilt.

| Schicht | Zuständigkeit | Beispiel |
| ------- | ------------ | ------- |
| **Model** | Datenbankzugriff, Datenlogik | `Order::findAll()` |
| **Controller** | Ablaufsteuerung, Entscheidungen | Welche Daten brauche ich? Wohin leite ich weiter? |
| **View** | Darstellung als HTML | Tabelle mit Aufträgen ausgeben |

**Warum?**  
Wenn man z.B. das Design der Auftragsliste ändern will, fasst man nur die View an — nicht die Datenbanklogik. Änderungen bleiben lokal und beeinflussen nichts anderes.

### Beispiel: Ein Auftrag wird aufgerufen (`GET /orders/42`)

**Ziel:** Ein Mitarbeiter möchte die Detailseite von Auftrag Nr. 42 sehen — Titel, Status, zugehörige Tätigkeiten usw. Er klickt in der Liste auf den Auftrag, und der Browser ruft `/orders/42` auf.

Was passiert dabei Schritt für Schritt?

**1. Router** — `public/index.php`  
Der Router erkennt das Muster `/orders/{id}` und übergibt die Anfrage an den zuständigen Controller:

```php
$router->get('/orders/{id}', [OrderController::class, 'show']);
```

**2. Controller** — `OrderController::show()`  
Der Controller übernimmt die Steuerung. Er entscheidet, welche Daten er braucht, holt sie aus den Models und gibt sie an die View weiter:

```php
public function show(string $id): void
{
    $order      = Order::findById((int) $id);        // Auftrag aus DB
    $activities = Activity::findByOrder((int) $id);  // Tätigkeiten dazu
    $users      = User::findAllSorted();              // für das Formular
    $this->render('orders/show', compact('order', 'activities', 'users'));
}
```

**3. Model** — z.B. `Order::findById(42)`  
Das Model führt die SQL-Abfrage aus und gibt ein Array zurück — es weiß nichts davon, dass es gerade eine Web-Anfrage gibt:

```php
public static function findById(int $id): ?array
{
    $stmt = static::db()->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
```

**4. View** — `views/orders/show.php`  
Die (oder der) View ist der Programmteil der ausschließlich für die Darstellung zuständig ist. Sie bekommt die Auftragsdaten fertig aufbereitet als PHP-Variablen übergeben und baut daraus HTML: Titel des Auftrags als Überschrift, Status als farbiges Badge, Beschreibung als Fließtext usw. Woher die Daten kommen oder wie sie in der Datenbank gespeichert sind, interessiert sie nicht:

```php
<h1><?= e($order['title']) ?></h1>
<p>Status: <?= statusBadge($order['status']) ?></p>
```

**5. Antwort an den Browser**  
Der fertige HTML-Code wird an den Browser geschickt. Der Nutzer sieht die Auftragsseite.

**Gesamtablauf der Anfrage:**

```text
Browser       sendet GET /orders/42
  ↓
Router        erkennt /orders/{id}, wählt OrderController::show(42) aus
  ↓
Controller    fragt das Model nach Auftrag 42 und seinen Tätigkeiten
  ↓
Model         baut SQL-Abfragen zusammen, schickt sie an die Datenbank
  ↓
Datenbank     führt SQL aus, liefert Datensätze zurück an das Model
  ↓
Model         gibt die Daten als PHP-Array an den Controller zurück
  ↓
Controller    übergibt alle Daten ($order, $activities …) an die View
  ↓
View          empfängt die Daten vom Controller, baut HTML daraus zusammen
  ↓
Browser       zeigt das fertige HTML an
```

---

## Migrationen

Migrationen sind nummerierte SQL-Dateien die Änderungen an der Datenbankstruktur beschreiben.

**Das Problem ohne Migrationen:**  
Wenn Person A lokal eine neue Spalte in der Datenbank anlegt, weiß Person B davon nichts. Ihre Datenbank ist dann veraltet und der Code funktioniert nicht mehr.

**Die Lösung:**  
Jede Strukturänderung wird als SQL-Datei ins Repo eingecheckt:

```text
db/migrations/
├── 001_initial_schema.sql
├── 002_add_priority_to_orders.sql   ← neue Spalte
└── 003_add_image_path_to_orders.sql
```

Das Skript `php db/migrate.php` führt automatisch alle noch nicht ausgeführten Dateien aus. So ist jede Datenbank mit einem Befehl auf dem aktuellen Stand.

---

## SQLite

Eine Datenbank die komplett in einer einzigen Datei lebt — kein separater Datenbankserver nötig.

Ideal für kleine bis mittlere Projekte und für die Entwicklung: einfach zu installieren, einfach zu sichern (Datei kopieren), einfach zu löschen (Datei löschen).

Die Datenbankdatei (`*.sqlite`) ist in `.gitignore` eingetragen und landet nie im Repo — jede Person legt sich mit `php db/migrate.php` ihre eigene lokale Kopie an.

---

## Singleton-Muster

Ein Entwurfsmuster das sicherstellt, dass von einer Klasse genau eine Instanz existiert.

In diesem Projekt nutzen wir es für die Datenbankverbindung: Eine PDO-Verbindung aufzubauen kostet Zeit. Statt sie bei jedem Datenbankzugriff neu aufzubauen, wird sie beim ersten Aufruf erstellt und dann immer wieder verwendet:

```php
$pdo = Database::connection(); // baut Verbindung auf (nur beim ersten Aufruf)
$pdo = Database::connection(); // gibt dieselbe Verbindung zurück
```

**Aber wie — HTTP ist doch zustandslos?**  
Das ist ein wichtiger Unterschied: Das Singleton-Objekt überlebt *keine* Anfragen — es lebt nur *innerhalb* einer einzigen Anfrage.

In PHP wird das Skript bei jeder HTTP-Anfrage komplett neu gestartet. Es gibt keinen dauerhaft laufenden Serverprozess der sich etwas merkt (anders als z.B. bei Node.js). Das heißt: Sobald der Server die Antwort geschickt hat, wird das gesamte PHP-Skript beendet — alle Variablen, Objekte und die Datenbankverbindung werden aus dem Arbeitsspeicher gelöscht.

Das Singleton löst ein anderes Problem: Innerhalb *einer* Anfrage werden oft viele Datenbankzugriffe gemacht — `Order::findById()`, `Activity::findByOrder()`, `User::findAllSorted()` usw. Ohne Singleton würde jede dieser Methoden eine neue Verbindung aufbauen. Das Singleton stellt sicher, dass alle Aufrufe dieselbe Verbindung wiederverwenden:

```text
Anfrage startet       → PHP-Skript beginnt, noch keine DB-Verbindung
Order::findById(42)   → Database::connection() erstellt neue PDO-Verbindung, speichert sie
Activity::findBy...   → Database::connection() gibt dieselbe Verbindung zurück
User::findAllSorted() → Database::connection() gibt dieselbe Verbindung zurück
Antwort gesendet      → PHP-Skript endet, Verbindung wird freigegeben
```

Beim nächsten Seitenaufruf beginnt alles von vorne.

---

## CRUD

CRUD ist ein Kürzel für die vier Grundoperationen die man mit Datenbankeinträgen durchführen kann:

| Buchstabe | Operation | SQL | HTTP-Methode |
| --------- | --------- | --- | ------------ |
| **C**reate | Anlegen | `INSERT` | `POST` |
| **R**ead | Lesen | `SELECT` | `GET` |
| **U**pdate | Ändern | `UPDATE` | `POST` |
| **D**elete | Löschen | `DELETE` | `POST` |

Wenn man sagt "ein Controller implementiert CRUD", meint man: er kann Einträge anlegen, anzeigen, bearbeiten und löschen. Fast jeder Bereich der App (Aufträge, Kunden, Mitarbeiter) folgt diesem Muster.

Die HTTP-Methoden `PUT`, `PATCH` und `DELETE` existieren zwar, werden aber von HTML-Formularen nicht unterstützt — deshalb verwenden wir für Update und Delete ebenfalls `POST`.

---

## Controller-Hilfsmethoden

Die Basisklasse `Controller` stellt drei Methoden bereit die jeder Controller erbt:

**`render(string $view, array $data)`**  
Lädt eine View-Datei und gibt sie als HTML aus. Der `$data`-Parameter macht Variablen in der View verfügbar:

```php
$this->render('orders/index', ['orders' => $orders]);
// In der View ist dann $orders direkt verfügbar
```

Intern nutzt `render()` einen Output-Buffer: Die View wird zunächst unsichtbar gerendert, das Ergebnis als `$content` gespeichert und dann ins Layout eingebettet. Wie das genau funktioniert erklärt der Abschnitt [Layout und Templates](#layout-und-templates) weiter unten.

**`redirect(string $path)`**  
Schickt den Browser auf eine andere URL. Wird nach jedem erfolgreichen Speichern aufgerufen (siehe Post/Redirect/Get weiter unten):

```php
$this->redirect('/orders');  // Browser landet auf der Auftragsliste
```

**`clean(string $value)`**  
Entfernt Leerzeichen am Anfang und Ende eines Textes. Gibt `null` zurück wenn der Text danach leer ist — so entstehen keine leeren Strings in der Datenbank:

```php
$this->clean('  Hallo  ');  // → 'Hallo'
$this->clean('   ');        // → null
```

---

## Layout und Templates

Jede Seite der App sieht gleich aus: oben die Navbar, unten der Footer, dazwischen der eigentliche Inhalt. Damit Navbar und Footer nicht in jeder View-Datei wiederholt werden müssen, gibt es eine einzige Layout-Datei: `views/layout/main.php`.

**Wie `render()` das umsetzt:**

```php
// In Controller::render():
ob_start();                               // 1. Output-Buffer einschalten
require 'views/' . $view . '.php';        // 2. View-Datei ausführen — Output landet im Buffer
$content = ob_get_clean();               // 3. Buffer-Inhalt in $content speichern, Buffer leeren

require 'views/layout/main.php';          // 4. Layout laden — $content ist darin verfügbar
```

`ob_start()` / `ob_get_clean()` ist ein PHP-Trick: Normalerweise würde `require` den HTML-Output sofort an den Browser schicken. Mit dem Output-Buffer wird alles abgefangen und als String in `$content` gespeichert. Das Layout kann diesen String dann an der richtigen Stelle einsetzen:

```php
<!-- views/layout/main.php -->
<nav>...</nav>

<main>
    <?= $content ?>   <!-- hier erscheint der Inhalt der jeweiligen View -->
</main>

<footer>...</footer>
```

**Was das bedeutet:**  
Navbar, Footer, CSS-Links, JavaScript — alles steht einmal in `views/layout/main.php` und gilt automatisch für jede Seite. Will man z.B. den Footer ändern, fasst man nur diese eine Datei an.

---

## Post/Redirect/Get

Ein Muster das verhindert dass ein Formular beim Browser-Refresh doppelt abgeschickt wird.

**Das Problem:**  
Speichert man einen Auftrag mit POST und lädt danach die Seite neu, fragt der Browser: "Soll das Formular nochmal abgeschickt werden?" — und der Eintrag wird doppelt angelegt.

**Die Lösung:**  
Nach einem erfolgreichen POST nicht die View direkt ausgeben, sondern auf eine GET-Seite weiterleiten:

```text
1. Browser schickt POST /orders (Formular)
2. Controller speichert den Auftrag
3. Controller antwortet: redirect → GET /orders
4. Browser lädt die Liste neu (GET)
5. Browser-Refresh wiederholt nur den harmlosen GET
```

In jedem Controller sieht das so aus: `store()` und `update()` enden immer mit `$this->redirect(...)`.

### Flash-Meldungen

Post/Redirect/Get löst das Doppel-Submit-Problem — schafft aber ein neues: Nach dem Redirect weiß die GET-Seite nicht mehr, ob der POST erfolgreich war. Wie zeigt man dem Nutzer also "Passwort gespeichert" oder "Etwas ist schiefgelaufen"?

**Die Lösung:** Kurz vor dem Redirect wird eine Nachricht in die Session geschrieben. Der nächste GET-Request liest sie aus — und löscht sie sofort wieder, damit sie nur einmal erscheint.

```php
// Controller: vor dem Redirect
$_SESSION['flash'] = ['type' => 'success', 'text' => 'Passwort gespeichert.'];
$this->redirect('/users/' . $id);
```

```php
// views/layout/main.php: beim nächsten Seitenaufruf
if (!empty($_SESSION['flash'])) {
    // grüne oder rote Bootstrap-Meldung anzeigen
    unset($_SESSION['flash']);   // sofort löschen — erscheint nur einmal
}
```

Die Session dient hier als kurzzeitiger Briefkasten: eine Seite legt etwas rein, die nächste nimmt es raus. Daher der Name *Flash* — die Nachricht blitzt einmal auf und ist dann weg.

---

## Authentifizierung und Sessions

**Authentifizierung** bedeutet: Wer bist du? Die App prüft ob jemand wirklich der ist, der er behauptet zu sein — meistens über Benutzername und Passwort.

Das ist etwas anderes als **Autorisierung** (was darf jemand tun?). Beides ist wichtig, aber Authentifizierung kommt zuerst — man muss wissen wer jemand ist, bevor man entscheiden kann was er darf.

---

### Wie eine Session funktioniert

HTTP ist zustandslos — jede Anfrage ist für den Server ein Neuanfang, er erinnert sich an nichts. Eine **Session** löst das Problem: Der Server legt beim Login eine kleine Datei an und schickt dem Browser eine zufällige ID als Cookie zurück. Bei jeder weiteren Anfrage schickt der Browser diese ID mit, und der Server weiß: "Ah, das ist Peter."

```text
1. Browser: POST /login  (E-Mail + Passwort)
2. Server:  Passwort korrekt → Session anlegen, Cookie mit Session-ID senden
3. Browser: GET /orders  (Cookie wird automatisch mitgeschickt)
4. Server:  Session-ID aus Cookie lesen → weiß wer eingeloggt ist
```

In PHP wird eine Session mit `session_start()` gestartet. Danach kann man Daten darin speichern:

```php
$_SESSION['user_id']   = 42;       // Wir merken uns wer eingeloggt ist
$_SESSION['user_name'] = 'Peter';
```

Und beim Abmelden wird die Session gelöscht:

```php
session_destroy();
```

---

### Zugriffsschutz in diesem Projekt

In `public/index.php` prüfen wir bei jeder Anfrage ob eine Session existiert. Wenn nicht, landet man auf der Login-Seite:

```php
if (!in_array($currentPath, $publicRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
```

Diese Prüfung passiert *bevor* der Router die Anfrage an einen Controller weitergibt — so ist kein einziger Controller ohne Login erreichbar.

---

### Passwörter niemals im Klartext speichern

Passwörter werden nie direkt in der Datenbank gespeichert. Stattdessen wird ein **Hash** gespeichert — ein Fingerabdruck des Passworts der sich nicht rückrechnen lässt:

```php
// Beim Anlegen eines Benutzers:
$hash = password_hash('meinPasswort', PASSWORD_BCRYPT);
// $hash sieht z.B. so aus: $2y$10$abcdefgh...

// Beim Login:
password_verify('meinPasswort', $hash);  // → true
password_verify('falschesPasswort', $hash);  // → false
```

Selbst wenn jemand die Datenbank stiehlt, kann er die Passwörter nicht lesen.

---

### Session-Regenerierung

Nach einem erfolgreichen Login rufen wir `session_regenerate_id(true)` auf. Das klingt technisch, hat aber einen wichtigen Grund: Es verhindert **Session-Fixation** — einen Angriff bei dem jemand eine fremde Session-ID einschleust und dann nach dem Login dieselbe ID übernimmt. Durch die Regenerierung bekommt der eingeloggte Benutzer eine neue, unvorhersehbare ID.

---

### GET-Parameter als Zustand

Manchmal hat eine Seite einen Zustand der kein Login-Zustand ist: ein aktiver Filter, eine Sortierung, eine Seitenzahl. Man könnte das in der Session speichern — aber dann gehen zwei Dinge verloren:

- **Teilbarkeit**: Wer die URL kopiert, bekommt nicht denselben Zustand zu sehen.
- **Browser-Navigation**: Zurück- und Vorwärts-Button funktionieren nicht wie erwartet.

**Die Alternative:** Zustand in der URL speichern, als GET-Parameter.

```text
/orders?status[]=open&status[]=in_progress&sort=priority&dir=asc
```

Der Controller liest die Parameter aus `$_GET` und gibt sie an das Model weiter. Der Seitenaufruf ist dadurch vollständig in der URL beschrieben — Reload, Kopieren, Bookmarken und Browser-Back funktionieren alle korrekt.

**Wann Session, wann GET-Parameter?**

| Situation | Wohin damit? |
| --------- | ------------ |
| Wer ist eingeloggt? | Session — gehört nicht in die URL |
| Flash-Meldung nach Redirect | Session — einmalig, kein URL-Ballast |
| Aktiver Filter in einer Liste | GET-Parameter — URL soll teilbar sein |
| Aktuelle Seite in einer Tabelle | GET-Parameter — Browser-Back soll funktionieren |

---

## Autorisierung und Rollen

Autorisierung beantwortet die Frage: Was darf dieser Benutzer tun?

In diesem Projekt gibt es drei Rollen — `admin`, `coordinator` und `member` — die in der Datenbank pro Benutzer gespeichert sind. Nach dem Login wird die Rolle in der Session gemerkt:

```php
$_SESSION['user_role'] = $user['role'];  // z.B. 'admin'
```

Ab diesem Moment ist die Rolle bei jeder Anfrage bekannt und kann geprüft werden.

---

### Wie `requireRole()` funktioniert

Die Basisklasse `Controller` stellt eine Methode bereit, die den Zugriff auf eine Controller-Methode einschränkt:

```php
// Nur Admins dürfen weiter — alle anderen bekommen HTTP 403
$this->requireRole('admin');

// Admin oder Koordinator dürfen weiter
$this->requireRole('admin', 'coordinator');
```

Ist die Rolle des aktuellen Benutzers nicht in der Liste, wird sofort eine Fehlerseite ausgegeben und die Ausführung beendet. Der eigentliche Code dahinter wird nie erreicht.

Das sieht in einem Controller dann so aus:

```php
public function edit(string $id): void
{
    $this->requireRole('admin');   // ← Zugriffsschutz, erste Zeile
    $user = User::findById((int) $id);
    // ...
}
```

**Wichtig:** Der Schutz sitzt im Controller, nicht in der View. Views können Buttons ausblenden — aber das ist nur Komfort. Ein technisch versierter Benutzer könnte die URL trotzdem manuell aufrufen. Deswegen muss die echte Prüfung immer serverseitig im Controller stattfinden.

---

### Bereichsabhängige Prüfungen

Manchmal reicht eine einfache Rollenprüfung nicht aus. Bei Aufträgen gilt z.B.: Ein Mitarbeiter darf einen Auftrag nur bearbeiten wenn er ihm zugewiesen ist. Das prüft der Controller anhand konkreter Daten:

```php
private function canEditOrder(array $order): bool
{
    // Admin und Koordinator dürfen immer
    if (in_array($this->currentRole(), ['admin', 'coordinator'], true)) {
        return true;
    }
    // Mitarbeiter nur wenn er der Zugewiesene ist
    return $this->currentRole() === 'member'
        && $this->currentUserId() === (int) $order['assigned_user_id'];
}
```

---

### Feldbasierte Einschränkungen

Manchmal darf jemand zwar ein Formular ausfüllen, aber nicht alle Felder ändern. Dann hilft es nicht, das Feld nur im HTML auszublenden — `disabled`-Felder werden vom Browser nicht mitgeschickt, aber jemand könnte das Formular trotzdem manuell mit dem fehlenden Feld abschicken.

Die Lösung: Im Controller wird geprüft welche Felder der aktuelle Benutzer ändern darf, und nur diese werden gespeichert:

```php
$data = ['name' => ..., 'notes' => ...];  // darf jeder Koordinator ändern

if ($this->currentRole() === 'admin') {
    $data['email'] = ...;   // nur Admin darf E-Mail und Telefon ändern
    $data['phone'] = ...;
}

Customer::update((int) $id, $data);
```

Schickt ein Koordinator trotzdem ein `email`-Feld mit — es wird schlicht ignoriert.

---

Die vollständige Tabelle wer was darf steht in [docs/berechtigungen.md](berechtigungen.md).

---

## .htaccess und der `public/`-Ordner

Nur der Ordner `public/` ist vom Webserver erreichbar. Alle anderen Ordner (`src/`, `config/`, `db/`) liegen außerhalb des Web-Roots und können nicht direkt über den Browser aufgerufen werden.

Das verhindert z.B. dass jemand die Datenbankdatei oder Konfigurationsdateien direkt herunterladen kann.

Die Datei `public/.htaccess` sorgt dafür dass alle Anfragen durch `public/index.php` laufen — den zentralen Einstiegspunkt der App.

---

## Code-Stil-Konventionen

Konventionen sind Vereinbarungen darüber wie Code geschrieben wird — nicht weil es die einzig richtige Art ist, sondern damit alle im Team denselben Code auf Anhieb lesen können.

### Groß- und Kleinschreibung

| Was | Schreibweise | Beispiel |
| --- | ------------ | ------- |
| Klassen | `PascalCase` | `OrderController`, `Database` |
| Methoden & Funktionen | `camelCase` | `findAll()`, `markAsReturned()` |
| Variablen | `camelCase` | `$orderId`, `$assignedUser` |
| Konstanten | `UPPER_SNAKE_CASE` | `DB_PATH` |
| Dateinamen (Klassen) | wie die Klasse | `OrderController.php` |
| Dateinamen (Views) | `snake_case` | `order_form.php` |
| Datenbankspalten | `snake_case` | `created_at`, `assigned_user_id` |

**Warum fängt eine Klasse groß an?**  
In PHP (und den meisten anderen Sprachen) ist das eine weitverbreitete Konvention: Klassen beginnen mit einem Großbuchstaben, Variablen und Funktionen mit einem Kleinbuchstaben. So sieht man auf den ersten Blick ob man es mit einer Klasse (`Order`) oder einer Variablen (`$order`) zu tun hat — auch wenn der Name derselbe ist.

```php
$order = new Order();  // Order = Klasse (groß), $order = Variable (klein)
```

### Einrückung & Formatierung

- 4 Leerzeichen pro Einrückungsebene (keine Tabs)
- Öffnende geschweifte Klammer `{` bleibt in derselben Zeile wie die Funktion
- Zwischen Methoden eine Leerzeile

### Sprache im Code

- Bezeichner (Variablen, Methoden, Klassen) auf **Englisch**
- Kommentare auf **Deutsch** — aber nur wenn das Warum nicht offensichtlich ist
- Commit-Nachrichten auf **Deutsch**
