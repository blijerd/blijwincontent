#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/../.."

PHP_BIN="${PHP_BIN:-php}"

if [ -x "/opt/homebrew/opt/php@8.5/bin/php" ]; then
    PHP_BIN="/opt/homebrew/opt/php@8.5/bin/php"
fi

if [ ! -f "vendor/autoload.php" ]; then
    echo "Composer dependencies are missing. Run composer install before this deploy hook."
    exit 1
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
