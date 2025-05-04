[![de](./.github/badge-de.svg)](README-de.md)

# OpenSim Control Panel

A web interface based on PHP for users and admins of OpenSimulator grids. It allows users to register (when invited) and manage their own accounts, and admins to easily manage user accounts and regions.

## Installing / Upgrading

The OpenSimulator grid (ROBUST + at least one region) has to be setup beforehand, as OS-CPL uses its database tables. Custom OS-CPL tables are prefixed with `mcp_`.

### Docker
The official docker image contains:
- PHP with PHP-FPM and all required extensions, securely configured
- nginx as a reverse proxy for requests to PHP-FPM and for serving static files
- Automatic generation of config.ini with values from environment variables
- Integrated cron, configured to run OS-CPL cron jobs

Custom configuration is done using the following environment variables:
| Name | Required | Default | Description |
| -------- | ------- | ------- | ------- |
| DOMAIN | ✓ | - | Public host name of the control panel |
| DB_HOST | ✓ | - | Database server hostname |
| DB_DATABASE | ✓ | - | Database name |
| DB_USER | ✓ | - | Username for database access |
| DB_PASSSWORD | ✓ | - | Password for database access |
| SMTP_HOST | for "forgot password" | - | Mail server hostname |
| SMTP_PORT | - | 465 | Mail server port (Using 465 (SMTPS port) is recommended) |
| SMTP_SENDER | for "forgot password" | noreply@DOMAIN | Sender e-mail address |
| SMTP_SENDER_DISPLAY | - | GRID_NAME Support | Sender display name |
| SMTP_PASS | for "forgot password" | - | Mail server password |
| GRID_NAME | ✓ | - | Name of your grid |
| GRID_DESCRIPTION | - | - | Description shown on the grid splash screen |
| GRID_URL | - | http://DOMAIN:8002 | Grid home URL |
| PASSWORD_MIN_LENGTH | - | 8 | Minimum length for account passwords |
| TOS_URL | - | https://DOMAIN/tos.html | Link to the grid's terms of service |
| DEFAULT_AVATAR_NAME | ✓ | Example | Name of the default avatar |
| DEFAULT_AVATAR_UUID | ✓ | 00000000-0000-0000-0000-000000000000 | UUID of the default avatar |
| RESTCONSOLE_HOST | for IAR export | - | OpenSimulator REST console IP/host |
| RESTCONSOLE_PORT | - | 9001 | OpenSimulator REST console port |
| RESTCONSOLE_USER | for IAR export | - | REST console username |
| RESTCONSOLE_PASSWORD | for IAR export | - | REST console password |
| RESTCONSOLE_IAR_PATH | for IAR export | - | Path of the directory IAR files are saved to, as seen by the OpenSimulator instance|
| CRON_KEY | - | (randomly generated on each start) | API key needed to trigger cronjobs |

### Manual

OS-CPL requires at least PHP 8 with the following extensions:
1. php-curl
2. php-pdo_mysql
3. php-xml
4. php-intl
For better performance, it is recommended to install `php-apcu`.

Once your environment is set up, follow these steps:
1. Download the latest release as archive or with `git clone`
2. Run `composer install` to install dependencies
3. Compile style sheets and scripts using `npm install && npm run build`
4. Move the directories `app`, `data`, `public`, `vendor`, `locales` and `templates` into your web directory (e.g. /var/www)
5. Modify `config.example.ini` as needed, rename it to `config.ini` and move it into the web directory
6. Set the web server's root directory (Apache: `DocumentRoot`, nginx: `root`) to the path of `public`
7. Set the web server's index to `index.php`

When upgrading from an older version, follow steps 1-4, but keep the contents of `data` from the old installation.

### Post-Install

#### Add assets
To customize your installation, add the following assets:

| Path | Description |
| ---- | ------------ |
| favicon.png | Web site's favicon |
| data/img/*.png | Images to include in the splash screen slide show |

Paths are relative to `/app` for Docker installs and relative to the web directory for manual installs.

#### Share the IAR directory

IAR export is requested through the REST console by the OpenSimulator instance. The target directory has to be accessible for OpenSimulator and OS-CPL (e.g. through Docker bind mounts or a network file system). OS-CPL expects the IAR files in `data/iars`.
