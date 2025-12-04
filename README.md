# Myddleware YunoHost App

This package installs the [Myddleware](https://www.myddleware.com/) data integration platform on a YunoHost server. It relies on the standard PHP-FPM stack and follows the packaging guidelines for YunoHost applications.

## Features
- Serves the upstream Myddleware release behind NGINX and PHP-FPM
- Uses a dedicated system user and permission declaration
- Provides backup, restore, upgrade, and change-url scripts aligned with YunoHost helpers

See the documentation in the `doc/` folder for additional details.
