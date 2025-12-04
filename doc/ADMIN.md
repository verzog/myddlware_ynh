# Administration

Myddleware is installed under `/var/www/__APP__` with a dedicated system user named `__APP__` and served through PHP-FPM (`php__PHP_VERSION__`).

Key service locations:
- NGINX configuration: `/etc/nginx/conf.d/__DOMAIN__.d/__APP__.conf`
- PHP-FPM pool: `/etc/php/__PHP_VERSION__/fpm/pool.d/__APP__.conf`
- Data directory: `/var/lib/__APP__`

To apply configuration changes, reload NGINX and PHP-FPM:
```
sudo systemctl reload nginx
sudo systemctl reload php__PHP_VERSION__-fpm
```
