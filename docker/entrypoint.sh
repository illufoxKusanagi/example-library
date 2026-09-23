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
    if [ "${APP_ENV:-production}" = "production" ]; then
        echo "Error: APP_KEY environment variable is required in production!" >&2
        exit 1
    fi
    echo "Notice: APP_KEY not provided, generating fallback key..."
    export APP_KEY=$(php artisan key:generate --show --no-interaction)
fi

# Ensure stale dev hot-reload flag is removed in container
rm -f public/hot || true

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

# Cache in production; clear stale caches in development
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
fi

exec "$@"
