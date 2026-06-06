#!/bin/bash

# Common variables and helpers for Myddleware YunoHost package

phpversion=8.2

# Node.js is required to build the Webpack Encore frontend assets.
# Node 20 covers Encore 4. YunoHost has no manifest.toml field for this,
# so it is pinned here alongside phpversion (single source of truth).
nodejs_version=20
