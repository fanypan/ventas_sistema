#!/bin/sh
set -e
cd /var/www/html

/usr/local/bin/ensure-storage.sh

if [ -d /opt/storage-defaults ]; then
  cp -an /opt/storage-defaults/. storage/app/public/ 2>/dev/null || true
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "[entrypoint] APP_KEY vacío. Definilo en .env antes de levantar producción."
  exit 1
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
  php artisan storage:link --no-interaction
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEED:-false}" = "true" ] && [ ! -f storage/app/.docker_seeded ]; then
  php artisan db:seed --force --no-interaction
  mkdir -p storage/app
  touch storage/app/.docker_seeded
  chown www-data:www-data storage/app/.docker_seeded
fi

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

exec php-fpm
