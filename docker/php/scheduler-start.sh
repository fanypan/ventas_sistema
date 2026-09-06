#!/bin/sh
set -e
cd /var/www/html
/usr/local/bin/ensure-storage.sh
exec php artisan schedule:work
