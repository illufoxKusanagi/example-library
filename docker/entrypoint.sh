#!/bin/sh
set -e

# Bind explicitly to 0.0.0.0 on $PORT (or 10000/80) so Render detects the open port
PORT="${PORT:-10000}"
export SERVER_NAME=":${PORT}"
export CADDY_GLOBAL_OPTIONS="admin off"
export CADDY_SERVER_EXTRA_DIRECTIVES="bind 0.0.0.0"

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

# Run database migrations (fast check) and optional seeds
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || true
    if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
        php artisan db:seed --force --no-interaction || true
    fi
fi

# Cache configuration, routes, and Blade views for production speed
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
