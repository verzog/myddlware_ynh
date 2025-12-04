#!/bin/bash

#=================================================
# COMMON VARIABLES
#=================================================

app=$YNH_APP_INSTANCE_NAME
install_dir=${YNH_APP_INSTALL_DIR:-/var/www/$app}
data_dir=${YNH_APP_DATA_DIR:-/var/lib/$app}
YNH_PHP_VERSION=8.2
pkg_dependencies=("php${YNH_PHP_VERSION}" "php${YNH_PHP_VERSION}-cli" "php${YNH_PHP_VERSION}-fpm" "php${YNH_PHP_VERSION}-mysql" "php${YNH_PHP_VERSION}-xml" "php${YNH_PHP_VERSION}-curl" "php${YNH_PHP_VERSION}-intl" "php${YNH_PHP_VERSION}-zip" "php${YNH_PHP_VERSION}-gd" "unzip" "curl" "mariadb-server")
