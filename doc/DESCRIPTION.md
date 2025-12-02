# Myddleware for YunoHost

Myddleware is an open-source data integration and migration platform that lets you synchronise and move data between applications (CRMs, ERPs, LMSes such as Moodle, etc.).

This YunoHost package installs Myddleware behind nginx + PHP-FPM (PHP 8.4), with a MariaDB database and Node/Yarn build for the front-end assets.

After installation, visit the app URL and follow the Myddleware web installer to create the first admin user. The database and environment are already pre-configured via `.env.local`.
