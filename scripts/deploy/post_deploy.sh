#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/../.."

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

if [ -x "/opt/homebrew/opt/php@8.5/bin/php" ]; then
    PHP_BIN="/opt/homebrew/opt/php@8.5/bin/php"
fi

PHP_VERSION_ID="$("$PHP_BIN" -r 'echo PHP_VERSION_ID;')"

if [ "$PHP_VERSION_ID" -lt 80400 ]; then
    echo "PHP 8.4 or newer is required for deployment. Current PHP binary: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"
    exit 1
fi

if [ ! -f "vendor/autoload.php" ]; then
    if ! command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
        echo "Composer dependencies are missing and Composer was not found. Install Composer on the deployment host or set COMPOSER_BIN."
        exit 1
    fi

    "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
