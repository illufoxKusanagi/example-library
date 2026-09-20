#!/bin/sh
set -e

# Bind to the port provided by hosting environment ($PORT) or default to 80
if [ -n "$PORT" ]; then
    export SERVER_NAME=":${PORT}"
else
    export SERVER_NAME=":80"
fi

# Clean stale caches and rediscover packages for production environment
php artisan package:discover --ansi || true

# Create public storage symlink if it doesn't exist
php artisan storage:link --force || true


# Run database migrations and seeds (idempotent firstOrCreate)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || true
    if [ "${SEED_ON_DEPLOY:-true}" = "true" ]; then
        php artisan db:seed --force --no-interaction || true
    fi
fi

# Cache configuration, routes, and Blade views for production speed
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
