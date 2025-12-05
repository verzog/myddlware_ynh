#!/bin/bash

# Common variables and helpers for Myddleware YunoHost package

app=${YNH_APP_INSTANCE_NAME:-myddlware}
phpversion=8.2

# Load arguments when available, otherwise fall back to stored settings.
domain=${YNH_APP_ARG_DOMAIN:-$(ynh_app_setting_get --app="$app" --key=domain 2>/dev/null || true)}
path_url=${YNH_APP_ARG_PATH:-$(ynh_app_setting_get --app="$app" --key=path 2>/dev/null || true)}
[ -n "$path_url" ] && path_url=$(ynh_normalize_url_path --path "$path_url")
admin_mail=${YNH_APP_ARG_ADMIN:-$(ynh_app_setting_get --app="$app" --key=admin 2>/dev/null || true)}
is_public=${YNH_APP_ARG_IS_PUBLIC:-$(ynh_app_setting_get --app="$app" --key=is_public 2>/dev/null || true)}
final_path=${YNH_APP_ARG_FINALPATH:-$(ynh_app_setting_get --app="$app" --key=final_path 2>/dev/null || echo "/var/www/$app")}

# Database credentials
_db_name() {
    ynh_sanitize_dbid --db_name "$app"
}

db_name=$(ynh_app_setting_get --app="$app" --key=db_name 2>/dev/null || _db_name)
db_user=$db_name
