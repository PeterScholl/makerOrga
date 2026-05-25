# Konzepte & Begriffe

Kurze Erklärungen zu Technologien und Mustern die in diesem Projekt vorkommen.  
Kein Anspruch auf Vollständigkeit — nur das Wichtigste zum Loslegen.

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
Alle heruntergeladenen Pakete — und eine wichtige Datei: `vendor/autoload.php`. Diese eine Datei lädt man am Anfang des Programms ein, und danach sind alle Klassen aus allen Paketen automatisch verfügbar. In diesem Projekt nutzen wir das auch für unsere eigenen Klassen (siehe [Autoloader](#singleton-muster)).

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
