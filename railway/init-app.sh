#!/usr/bin/env sh
set -e

php artisan config:clear
php artisan view:clear
php artisan route:clear

php artisan migrate --force
php artisan storage:link || true

php artisan config:cache
php artisan view:cache
