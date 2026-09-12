# =============================================================================
# Multi-stage Dockerfile for Laravel + Vue (Inertia) — Dokploy / VPS
# Base: PHP 8.4 FPM Alpine | DB: PostgreSQL (external service)
# =============================================================================

# ---- Stage 1: Build frontend assets ----
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Generate Wayfinder stubs (real routes are generated at runtime by entrypoint)
COPY docker/generate-wayfinder-stubs.sh /tmp/generate-stubs.sh
RUN chmod +x /tmp/generate-stubs.sh && sh /tmp/generate-stubs.sh

ENV DOCKER_BUILD=true
RUN npm run build


# ---- Stage 2: Install PHP dependencies ----
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
COPY database/ database/
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY routes/ routes/
COPY config/ config/
COPY artisan artisan

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts


# ---- Stage 3: Production image ----
FROM php:8.4-fpm-alpine AS production

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        postgresql-dev \
        postgresql-client \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        curl \
        zip \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy built frontend assets from stage 1
COPY --from=frontend /app/public/build public/build

# Copy PHP vendor from stage 2
COPY --from=vendor /app/vendor vendor

# Remove dev / build files not needed in production
RUN rm -rf node_modules tests .git .github docker.txt project.txt \
    resources/js/.eslintrc* eslint.config.js .prettierrc .prettierignore \
    phpunit.xml pint.json .editorconfig

# Create required directories
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    /var/lib/nginx/tmp/client_body \
    /run/nginx

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ---- Config files ----
COPY docker/nginx.conf        /etc/nginx/nginx.conf
COPY docker/default.conf      /etc/nginx/http.d/default.conf
COPY docker/php-fpm.conf      /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini           /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf  /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh     /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
