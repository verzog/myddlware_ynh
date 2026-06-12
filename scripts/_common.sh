#!/bin/bash

# Common variables and helpers for Myddleware YunoHost package

phpversion=8.2

# Node.js is required to build the Webpack Encore frontend assets.
# Node 20 covers Encore 4. YunoHost has no manifest.toml field for this,
# so it is pinned here alongside phpversion (single source of truth).
nodejs_version=20

# Normalize every hard-coded image/base path in Myddleware's frontend JS to the
# absolute install path, before 'yarn build'. Upstream constructs these URLs in
# ~6 different ways, all assuming a root install or a visible "/public/" URL
# segment. On a YunoHost subpath install the browser URL is "<path>/..." (nginx
# aliases it to public/ server-side, so "/public/" never appears), which means:
#   - getBaseUrl()/getSolutionImagePath() hit their root-install fallbacks and
#     drop the "<path>" prefix, so the document-detail AJAX panels and solution
#     logos 404;
#   - path_img / path_img_modal resolve to "/build/images/..." (no prefix);
#   - connector status icons prepend a now-wrong "../" to the (already absolute)
#     path_img, doubling the path.
# This complements the path_img replacements done inline in the caller and, with
# them, points every image/base URL at "<path>/build/images/...". Idempotent
# (literal find/replace) and root-safe (empty path -> "/build/images/...").
# Relies on $install_dir, $path and $app from the YunoHost environment.
myddleware_fix_asset_paths() {
    local base="${path%/}"            # "" for root, "/myddleware" otherwise
    local img="${base}/build/images/"
    local a="$install_dir/assets"

    # path_img in regle.js's "public" branch (the "/build/images/" branch is
    # handled by the caller's path_img replacements).
    sed -i "s#path_img = '../../build/images/'#path_img = '${img}'#g" "$a/js/regle.js"

    # path_img_modal literals used by the rule "createout" connector modal.
    sed -i "s#\"../../../build/images/\"#\"${img}\"#g" "$a/js/regle.js"

    # Connector status icons: path_img is absolute now, so upstream's "../" and
    # pathString relative prefixes must go or they double the path.
    sed -i -E "s#\"\.\./\" *\+ *path_img#path_img#g; s#pathString *\+ *path_img#path_img#g" \
        "$a/js/connector.js" "$a/js/connector_detail.js"

    # document-detail URL helpers: inject the install path into the root-install
    # fallbacks so getBaseUrl() (interactive AJAX panels) and getSolutionImagePath()
    # (solution logos) resolve under "<path>" instead of the bare domain root.
    local utils="$a/js/document-detail/document-detail-url-utils.js"
    sed -i "s#return window.location.origin;#return window.location.origin + '${base}';#g" "$utils"
    sed -i "s#return '/build/images/solution/';#return '${base}/build/images/solution/';#g" "$utils"

    chown -R "$app:$app" "$a"
}
