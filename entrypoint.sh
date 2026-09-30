#!/bin/sh
set -eu

cd /var/www/html
rm -f /tmp/app-ready

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

composer install --no-interaction --prefer-dist
php artisan config:clear --no-interaction

if ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --no-interaction
fi

php artisan migrate --no-interaction

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

touch /tmp/app-ready
exec docker-php-entrypoint "$@"
