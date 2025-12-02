
# Myddleware for YunoHost

[![Install Myddleware with YunoHost](https://install-app.yunohost.org/install-with-yunohost.svg)](https://install-app.yunohost.org/?app=myddleware)

## Overview
Myddleware is an open-source data integration and synchronization platform.  
This YunoHost package uses the *precompiled upstream build*, requiring **no NodeJS**.

## Installation
```
sudo yunohost app install https://github.com/YOUR_GITHUB/myddleware_ynh
```

## Configuration
The YunoHost admin panel exposes:
- MAILER_URL
- APP_DEBUG

Changes regenerate `.env.local`.

## CI
This repo includes:
- tests/tests.toml
- GitHub Actions CI workflow in `.github/workflows/ci.yml`
