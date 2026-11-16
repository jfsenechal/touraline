#!/usr/bin/env bash
# Clear Laravel/Filament caches and reset ownership & permissions.
# Usage: sudo ./clean.sh
set -euo pipefail

if [[ ${EUID} -ne 0 ]]; then
    echo "This script must be run as root (sudo $0)" >&2
    exit 1
fi

cd "$(dirname "$(readlink -f "$0")")"

# Print the user the web server runs as: the non-root owner of a running
# frankenphp/apache process, else the first known web server account that exists.
# Override with: sudo WEB_USER=name ./clean.sh
detect_web_user() {
    local uid name

    if [[ -n "${WEB_USER:-}" ]]; then
        echo "${WEB_USER}"
        return
    fi

    for name in frankenphp apache2 httpd; do
        uid="$(ps -C "${name}" -o uid= 2>/dev/null | awk '$1 != 0 { print $1; exit }')"
        if [[ -n "${uid}" ]]; then
            id -un "${uid}"
            return
        fi
    done

    for name in frankenphp www-data apache; do
        if id -u "${name}" &>/dev/null; then
            echo "${name}"
            return
        fi
    done

    echo "Unable to detect the web server user, set WEB_USER=name" >&2
    return 1
}

# Files belong to the user who invoked sudo (root when run directly as root)
OWNER_USER="${SUDO_USER:-root}"
OWNER="${OWNER_USER}:$(id -gn "${OWNER_USER}")"
WEB_USER="$(detect_web_user)"
WEB_GROUP="$(id -gn "${WEB_USER}")"
WRITABLE_DIRS=(storage bootstrap/cache)

echo "Owner: ${OWNER}"
echo "Web server user: ${WEB_USER}:${WEB_GROUP}"

# Caches & logs (removed before artisan boots so stale manifests can't break it)
rm -rf bootstrap/cache/*.php bootstrap/cache/filament/ storage/framework/views/*.php storage/logs/*.log

# optimize:clear covers config, route, view, event, cache and compiled files
php artisan optimize:clear --quiet
php artisan filament:optimize-clear --quiet

# Ownership
chown -R "${OWNER}" .
chown -R "${WEB_USER}:${WEB_GROUP}" "${WRITABLE_DIRS[@]}"

# Permissions: 755 dirs / 644 files, skipping .git, vendor and node_modules (keeps their binaries executable)
find . \( -path ./.git -o -path ./vendor -o -path ./node_modules \) -prune -o -type d -exec chmod 755 {} + \
    -o -type f -exec chmod 644 {} +
chmod 755 artisan clean.sh

# Writable dirs: 775 dirs / 664 files, setgid so new files inherit the web server group
find "${WRITABLE_DIRS[@]}" -type d -exec chmod 2775 {} +
find "${WRITABLE_DIRS[@]}" -type f -exec chmod 664 {} +

# SQLite: the web server must write the database files and create journals beside them
if compgen -G "database/*.sqlite*" > /dev/null; then
    chown "${OWNER_USER}:${WEB_GROUP}" database
    chmod 2775 database
    chown "${WEB_USER}:${WEB_GROUP}" database/*.sqlite*
    chmod 664 database/*.sqlite*
fi

# .env readable by the web server only
[[ -f .env ]] && chown "${OWNER_USER}:${WEB_GROUP}" .env && chmod 640 .env

echo "Done."
