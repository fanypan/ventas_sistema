#!/bin/sh
set -e
cd /var/www/html
/usr/local/bin/ensure-storage.sh
. /usr/local/bin/ensure-app-key.sh
exec php artisan schedule:work
