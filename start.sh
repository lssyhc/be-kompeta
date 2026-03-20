#!/usr/bin/env sh
set -eu

echo "[start] Discovering packages..."
php artisan package:discover --ansi

echo "[start] Preparing Laravel caches..."
mkdir -p resources/views storage/framework/views
php artisan config:cache
php artisan route:cache

echo "[start] Running database migrations..."
php artisan migrate --force

echo "[start] Ensuring storage symlink..."
php artisan storage:link || true

PORT="${PORT:-10000}"
echo "[start] Starting Laravel on 0.0.0.0:${PORT}"
php artisan serve --host=0.0.0.0 --port="${PORT}"
