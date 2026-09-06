#!/bin/sh
set -e
cd /var/www/html

/usr/local/bin/ensure-storage.sh

if [ -d /opt/storage-defaults ]; then
  cp -an /opt/storage-defaults/. storage/app/public/ 2>/dev/null || true
fi

# No tocar storage/app/backups: en Docker Desktop (Windows) el bind mount
# no acepta chown y con set -e tumba PHP antes de php-fpm.
echo "[entrypoint] permisos de storage..."
chown -R www-data:www-data \
  storage/framework \
  storage/logs \
  storage/app/public \
  bootstrap/cache \
  || echo "[entrypoint] aviso: chown incompleto (típico en Windows)"
chmod -R ug+rwx \
  storage/framework \
  storage/logs \
  storage/app/public \
  bootstrap/cache \
  || echo "[entrypoint] aviso: chmod incompleto (típico en Windows)"

if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "[entrypoint] APP_KEY vacío. Definilo en .env antes de levantar producción."
  exit 1
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
  echo "[entrypoint] storage:link..."
  php artisan storage:link --no-interaction
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "[entrypoint] migrate..."
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEED:-false}" = "true" ] && [ ! -f storage/app/.docker_seeded ]; then
  echo "[entrypoint] db:seed..."
  php artisan db:seed --force --no-interaction
  mkdir -p storage/app
  touch storage/app/.docker_seeded
  chown www-data:www-data storage/app/.docker_seeded 2>/dev/null || true
fi

echo "[entrypoint] config/route/view cache..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "[entrypoint] php-fpm"
exec php-fpm
