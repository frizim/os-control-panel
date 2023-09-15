# MCP: OpenSim-Gridverwaltung

Das MCP ist ein PHP-Webinterface für Benutzer und Administratoren von OpenSimulator-Grids. Es ermöglicht Benutzern die Registrierung (auf Einladung) und Verwaltung des eigenen OpenSimulator-Accounts im Self-Service-Verfahren. Administratoren können Accounts und Regionen einfacher verwalten.

## Installation

Voraussetzung ist, dass die Datenbankstruktur eines OpenSimulator-Grids bereits existiert. Das MCP muss vor der Nutzung mit den Zugangsdaten derselben Datenbank konfiguriert werden. Eigene Tabellen des MCP besitzen zur Vermeidung von Konflikten den Präfix `mcp_`.

Folgende PHP-Erweiterungen werden benötigt:
1. php-curl
2. php-mysql (PDO)
3. php-xml
4. php-xmlrpc

Für bessere Performance kann optional `php-apcu` installiert werden.

Die Installation läuft folgendermaßen ab:
1. Gewünschtes Release als ZIP/TAR-Archiv oder per `git clone` herunterladen
2. Verzeichnisse `app`, `data`, `lib`, `public` und `templates` in das Verzeichnis des Webservers entpacken
3. Öffentliche Stammverzeichnis des Webservers (Apache: `DocumentRoot`, nginx: `root`) auf Pfad zum Verzeichnis `public` ändern
4. Index des Webservers auf index.php ändern, falls erforderlich
5. Beispielkonfiguration `config.example.ini` anpassen, in `config.ini` umbenennen und in das Verzeichnis der in Schritt 2 entpackten Verzeichnisse verschieben

## Aktualisierung

Zur Aktualisierung müssen lediglich die Verzeichnisse `app`, `lib`, `public` und `templates`, sowie der Inhalt von `data` (außer `iars`) ersetzt werden.  
Die Migration der Datenbankstruktur erfolgt automatisch. Möglicherweise erforderliche Änderungen an der Konfiguration sind den Release-Informationen zu entnehmen.

## Entwickler

Die Abhängigkeiten des Frontends werden über [npm](https://www.npmjs.com/) verwaltet. Alle erforderlichen Pakete können nach dem Download des Repository über `npm install` heruntergeladen werden.
In `package.json` ist zudem ein Buildprozess (Befehl: `npm build`) definiert, durch den die Sass-Stylesheets im Verzeichnis `scss` kompiliert und optimiert und zusammen mit Schriftarten und Skripts im öffentlichen Webverzeichnis abgelegt werden.

Das Backend besitzt kein Dependency Management. Die einzige Abhängigkeit, [PHPMailer](https://github.com/PHPMailer/PHPMailer), wurde manuell in das Repository eingefügt. Der Autoloader sucht im Verzeichnis `lib` nach solchen externen Klassen.

### Verarbeitung von Anfragen

Das Skript `index.php` wird bei allen Anfragen aufgerufen. Die angeforderte Route wird als GET-Parameter `<Gruppe>=<Name>` übermittelt. Gruppen (momentan `api` und `page`), zugehörige Seiten und die assoziierte `RequestHandler`-Subklasse sind in `Mcp.php` definiert.

Ist zu einer Anfrage eine Route definiert, wird der zugehörige `RequestHandler` erzeugt. Ist eine `Middleware`-Klasse für diesen definiert, ist die weitere Verarbeitung von dem Rückgabewert von `Middleware::canAccess()` abhängig.  
Schließlich wird je nach Methode der Anfrage `RequestHandler::get()` bzw. `RequestHandler::post()` aufgerufen.