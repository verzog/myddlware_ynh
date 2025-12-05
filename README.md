# Myddleware for YunoHost

Myddleware is an open-source middleware platform that synchronizes and transforms data between business applications. This package brings Myddleware to YunoHost with a dedicated PHP-FPM pool, NGINX configuration, and Composer-managed dependencies.

## Features
- Composer-based installation of the official Myddleware release (v4.2.2)
- Dedicated PHP-FPM pool and NGINX vhost modeled after the Kimai2 YunoHost package
- Automatic database provisioning (MariaDB/MySQL)
- Production-ready `.env.local` generation with secure secrets
- Backup and restore hooks for YunoHost

## Installation
1. Ensure your server meets the requirements: YunoHost 11.2+, PHP 8.2+, MariaDB/MySQL, and Composer support.
2. Install the app via the YunoHost admin or with the CLI:
   ```bash
   yunohost app install https://github.com/verzog/myddlware_ynh --force
   ```
3. Provide the domain, path, administrator email, and whether the app should be public when prompted.

## Upgrade
The upgrade script backs up existing files, fetches the latest upstream release, reinstalls Composer dependencies, preserves `.env.local`, and reloads PHP-FPM and NGINX.

## Backup and Restore
- **Backup**: The `backup` script archives the application directory, configuration templates, and the database.
- **Restore**: The `restore` script recreates the system user, restores files, reconfigures NGINX/PHP-FPM, and imports the database dump.

## Debugging
- View logs in `/var/log/nginx/myddlware-access.log`, `/var/log/nginx/myddlware-error.log`, and PHP-FPM logs in `/var/log/php8.2-fpm/myddlware.log`.
- Regenerate configuration after edits with `yunohost app ssowatconf regen` and `yunohost service regen-conf nginx --force`.
- Run Composer commands from `/var/www/myddlware` using `sudo -u myddlware` for correct permissions.

## Documentation
- Project documentation and usage guides: <https://github.com/Myddleware/myddleware/wiki>
- YunoHost packaging guidelines: <https://yunohost.org/en/packaging_apps>
