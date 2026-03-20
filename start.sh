#!/usr/bin/env sh
set -eu

echo "[start] Installing composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "[start] Preparing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] Running database migrations..."
php artisan migrate --force

echo "[start] Ensuring storage symlink..."
php artisan storage:link || true

PORT="${PORT:-10000}"
echo "[start] Starting Laravel on 0.0.0.0:${PORT}"
php artisan serve --host=0.0.0.0 --port="${PORT}"
