#!/bin/sh
# El volumen storage_data arranca vacío: Blade exige estas carpetas.
cd /var/www/html
mkdir -p storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  storage/app/public \
  storage/app/backups \
  bootstrap/cache
