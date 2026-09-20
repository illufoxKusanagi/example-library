# -------------------------------------------------------------
# Stage 1: Install Composer PHP Dependencies
# -------------------------------------------------------------
FROM composer:2 AS composer
WORKDIR /app

COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# -------------------------------------------------------------
# Stage 2: Build Frontend Assets (Vite & Tailwind CSS v4)
# -------------------------------------------------------------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
# Copy vendor from composer stage so Flux UI CSS is available during build
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# -------------------------------------------------------------
# Stage 3: Production Runtime with FrankenPHP (PHP 8.5)
# -------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.5-bookworm AS runner

# Install essential PHP extensions for Laravel (SQLite, PostgreSQL, MySQL)
RUN install-php-extensions \
    pdo_sqlite \
    pdo_pgsql \
    pdo_mysql \
    zip \
    bcmath \
    pcntl \
    intl \
    opcache

# Remove file capabilities to prevent EPERM (Operation not permitted) under Render's no_new_privs sandbox
RUN setcap -r /usr/local/bin/frankenphp

# Production environment defaults
ENV APP_ENV="production"
ENV APP_DEBUG="false"
ENV LOG_CHANNEL="stderr"
ENV SERVER_NAME=":10000"
ENV CADDY_GLOBAL_OPTIONS="admin off"
ENV CADDY_SERVER_EXTRA_DIRECTIVES="bind 0.0.0.0"

EXPOSE 10000 80

WORKDIR /app

# Copy application code
COPY . .

# Copy composer vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy compiled Vite assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Ensure directory structure and permissions for web server
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public/covers \
    bootstrap/cache \
    database && \
    rm -f bootstrap/cache/*.php && \
    php artisan package:discover --ansi && \
    chown -R www-data:www-data storage bootstrap/cache database && \
    chmod -R 775 storage bootstrap/cache database

# Setup startup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
