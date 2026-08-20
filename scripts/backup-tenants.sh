#!/bin/sh
set -e
cd "$(dirname "$0")/.."
php artisan tenants:backup
