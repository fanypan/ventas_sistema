#!/bin/sh
set -e
cd /var/www/html

WWWUSER="${WWWUSER:-1000}"
WWWGROUP="${WWWGROUP:-1000}"

mkdir -p /tmp/composer/cache /tmp/npm-cache

mkdir -p storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  storage/app/public \
  bootstrap/cache

# Corrige archivos creados como root en arranques anteriores.
chown -R "${WWWUSER}:${WWWGROUP}" storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ ! -d vendor ]; then
  echo "[entrypoint] installing composer dependencies..."
  gosu "${WWWUSER}:${WWWGROUP}" composer install --prefer-dist --no-interaction
fi

if [ ! -f .env ]; then
  cp .env.example .env
  chown "${WWWUSER}:${WWWGROUP}" .env
fi

if ! gosu "${WWWUSER}:${WWWGROUP}" grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  gosu "${WWWUSER}:${WWWGROUP}" php artisan key:generate --force --no-interaction
fi

gosu "${WWWUSER}:${WWWGROUP}" php artisan config:clear

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
  gosu "${WWWUSER}:${WWWGROUP}" php artisan storage:link --no-interaction
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  gosu "${WWWUSER}:${WWWGROUP}" php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEED:-true}" = "true" ] && [ ! -f storage/app/.docker_seeded ]; then
  gosu "${WWWUSER}:${WWWGROUP}" php artisan db:seed --force --no-interaction
  mkdir -p storage/app
  touch storage/app/.docker_seeded
  chown "${WWWUSER}:${WWWGROUP}" storage/app/.docker_seeded
fi

exec gosu "${WWWUSER}:${WWWGROUP}" "$@"
