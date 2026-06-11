# =========================================================
# Stage 1: Composer dependencies
# =========================================================
FROM composer:2.8 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./

# Install production dependencies only (no dev)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =========================================================
# Stage 2: Production image
# =========================================================
# This is a pure API — no Vite/frontend build step needed.
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="blog-api"

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        zip \
        unzip \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        icu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy vendor from composer stage
COPY --from=composer-deps /app/vendor ./vendor

# Copy application source
COPY . .

# Copy nginx & supervisor configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy & make entrypoint executable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure required dirs exist then set permissions
RUN mkdir -p /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Render assigns a PORT env variable; default to 8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
