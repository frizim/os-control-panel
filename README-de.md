[![en](./.github/badge-en.svg)](README.md)

# OpenSim Control Panel (OS-CPL)

Ein PHP-Webinterface für Benutzer und Administratoren von OpenSimulator-Grids. Es ermöglicht Benutzern die Registrierung (auf Einladung) und Verwaltung des eigenen OpenSimulator-Accounts im Self-Service-Verfahren. Administratoren können Accounts und Regionen einfacher verwalten.

## Installation / Aktualisierung

Ein OpenSimulator-Grid (ROBUST + mindestens eine Region) muss bereits eingerichtet sein, da OS-CPL auf dessen Datenbanktabellen zugreift. Eigene Tabellen besitzen zur Vermeidung von Konflikten den Präfix `mcp_`.

### Docker
Das offizielle Docker-Image dieses Projekts beinhaltet:
- PHP mit PHP-FPM und allen benötigten Erweiterungen, sicher konfiguriert
- nginx als Reverse Proxy für PHP-FPM und Webserver für statische Dateien
- Automatische Erstellung der config.ini aus Umgebungsvariablen
- Integrierter Cron-Daemon zur Ausführung von OS-CPL Cronjobs

Die folgenden Umgebungsvariablen stehen zur Verfügung:
| Name | Benötigt | Standardwert | Beschreibung |
| -------- | ------- | ------- | ------- |
| DOMAIN | ✓ | - | Domain, über die die Seite erreichbar ist |
| DB_HOST | ✓ | - | Hostname des Datenbankservers |
| DB_DATABASE | ✓ | - | Name der Datenbank |
| DB_USER | ✓ | - | Benutzername für Datenbankzugriff |
| DB_PASSSWORD | ✓ | - | Passwort für Datenbankzugriff |
| SMTP_HOST | für "Passwort vergessen" | - | Hostname des Mailservers |
| SMTP_PORT | - | 465 | Port des Mailservers (empfohlen: SMTPS-Standardport) |
| SMTP_SENDER | für "Passwort vergessen" | noreply@DOMAIN | Absenderadresse von E-Mails |
| SMTP_SENDER_DISPLAY | - | GRID_NAME Support | Anzeigename des Absenders |
| SMTP_PASS | für "Passwort vergessen" | - | Passwort für E-Mail-Versand |
| GRID_NAME | ✓ | - | Name des verwalteten Grids |
| GRID_DESCRIPTION | - | - | Beschreibungstext für Grid-Splashscreen |
| GRID_URL | - | http://DOMAIN:8002 | URL des Grids
| PASSWORD_MIN_LENGTH | - | 8 | Mindestzeichenzahl für Account-Passwörter |
| TOS_URL | - | https://DOMAIN/tos.html | Link zu den Nutzungsbedingungen |
| DEFAULT_AVATAR_NAME | ✓ | Example | Name des Standardavatars |
| DEFAULT_AVATAR_UUID | ✓ | 00000000-0000-0000-0000-000000000000 | UUID des Standardavatars |
| RESTCONSOLE_HOST | für IAR-Download | - | Hostname der OpenSim-REST-Konsole |
| RESTCONSOLE_PORT | - | 9001 | Port der OpenSim-REST-Konsole |
| RESTCONSOLE_USER | für IAR-Download | - | Benutzername für Zugang zur REST-Konsole |
| RESTCONSOLE_PASSWORD | für IAR-Download | - | Passwort für Zugang zur REST-Konsole |
| RESTCONSOLE_IAR_PATH | für IAR-Download | - | Pfad zum IAR-Zielverzeichnis aus Sicht der OpenSim-Instanz |
| CRON_KEY | - | (bei Start automatisch generiert) | API-Key für Ausführung von Cronjobs |

### Manuell

Folgende PHP-Erweiterungen werden benötigt:
1. php-curl
2. php-pdo_mysql
3. php-xml
4. php-intl
Für bessere Performance kann optional `php-apcu` installiert werden.

Die Installation läuft folgendermaßen ab:
1. Aktuelles Release als Archiv oder per `git clone` herunterladen
2. Abhängigkeiten über `composer install` installieren
3. Frontend-Stylesheets und Skripte über `npm install && npm run build` kompilieren
4. Verzeichnisse `app`, `data`, `public`, `vendor`, `locales` und `templates` in das Webverzeichnis (z.B. /var/www) verschieben
5. `config.example.ini` anpassen, in `config.ini` umbenennen und in das Webverzeichnis
6. Stammverzeichnis des Webservers (Apache: `DocumentRoot`, nginx: `root`) auf Pfad zu `public` ändern
7. Index des Webservers auf `index.php` ändern

Zur Aktualisierung müssen die Schritte 1-4 erneut durchlaufen werden, jedoch ist der Inhalt von `data` aus der alten Installation beizubehalten.

### Nach der Installation

#### Dateien hinzufügen
Zur Anpassung der Installation können folgende Dateien hinzugefügt werden:

| Pfad | Beschreibung |
| ---- | ------------ |
| favicon.png | Favicon der Website |
| data/img/*.png | Bilder für Splash-Screen |

Pfade sind relativ zu `/app` für Docker-Installationen bzw. relativ zum Webverzeichnis für manuelle Installationen.

#### IAR-Verzeichnis teilen

IARs werden nach Anforderung per REST-Konsole durch die OpenSim-Instanz erstellt. Das Zielverzeichnis muss der OpenSim-Instanz und dem MCP zugänglich sein (ggf. über ein geteiltes Docker-Volume oder Netzwerkdateisystem). Das MCP muss die IARs unter `data/iars` vorfinden können.
