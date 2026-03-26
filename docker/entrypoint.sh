#!/bin/sh
set -e
cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=.\+' .env 2>/dev/null; then
    php artisan key:generate --force --ansi
fi

# Кэш с хоста (php artisan config:cache) монтируется в контейнер → внутри остаётся DB_HOST=127.0.0.1
rm -f bootstrap/cache/config.php 2>/dev/null || true
find bootstrap/cache -maxdepth 1 -name 'routes-*.php' -delete 2>/dev/null || true
php artisan optimize:clear --ansi 2>/dev/null || true

php artisan migrate --force --ansi

exec php artisan serve --host=0.0.0.0 --port=8000
