#!/bin/sh
set -e
cd /var/www/html
/usr/local/bin/ensure-storage.sh
. /usr/local/bin/ensure-app-key.sh

if [ "${HORIZON_ENABLED:-false}" = "true" ]; then
  exec php artisan horizon
fi

exec php artisan queue:work redis --sleep=1 --tries=3
