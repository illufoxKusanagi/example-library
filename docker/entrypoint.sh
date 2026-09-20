#!/bin/sh
set -e

# Bind to the port provided by hosting environment ($PORT) or default to 80
if [ -n "$PORT" ]; then
    export SERVER_NAME=":${PORT}"
else
    export SERVER_NAME=":80"
fi

# Use Render external URL if available and APP_URL is unset
if [ -n "$RENDER_EXTERNAL_URL" ] && [ -z "$APP_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Ensure APP_KEY exists so encrypter and session cookies never fail
if [ -z "$APP_KEY" ]; then
    echo "Notice: APP_KEY not provided, generating fallback key..."
    export APP_KEY=$(php artisan key:generate --show --no-interaction)
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
