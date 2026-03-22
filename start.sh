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

RUN_DEPLOY_SEED="${RUN_DEPLOY_SEED:-false}"
SEED_CLASS="${SEED_CLASS:-Database\\Seeders\\DatabaseSeeder}"

if [ "$RUN_DEPLOY_SEED" = "true" ]; then
	echo "[start] Running database seeder (${SEED_CLASS})..."
	php artisan db:seed --force --class="$SEED_CLASS"
else
	echo "[start] Skipping database seeder (RUN_DEPLOY_SEED=false)."
fi

echo "[start] Ensuring storage symlink..."
php artisan storage:link || true

PORT="${PORT:-10000}"
echo "[start] Starting Laravel on 0.0.0.0:${PORT}"
php artisan serve --host=0.0.0.0 --port="${PORT}"
