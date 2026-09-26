# ==============================================================================
# Multi-Stage Dockerfile for Cynaris Internship Laravel 13 Application
# Target Environment: Production & Local Parity Container Runtime
# Base PHP Version: PHP 8.4 Apache
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite, Tailwind CSS, Alpine.js)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Copy dependency manifests
COPY package.json package-lock.json* ./

# Install npm dependencies cleanly
RUN npm ci --prefer-offline --no-audit

# Copy source assets and configuration
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public

# Build compiled static assets into public/build
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: Install Composer Production Dependencies
# ------------------------------------------------------------------------------
FROM composer:2 AS composer-builder

WORKDIR /app

# Copy composer manifests
COPY composer.json composer.lock ./

# Copy directories referenced by PSR-4 autoloading
COPY app ./app
COPY database ./database

# Install production dependencies without dev tools, tests, or scripts
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# ------------------------------------------------------------------------------
# Stage 3: Production PHP 8.4 Apache Runtime
# ------------------------------------------------------------------------------
FROM php:8.4-apache

# Configure Apache DocumentRoot to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Install Linux system dependencies and required PHP extensions
# (PostgreSQL, SQLite, MySQL, Zip, BCMath, OPcache)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    pdo_mysql \
    bcmath \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Production PHP runtime optimizations
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'memory_limit=256M'; \
    echo 'upload_max_filesize=32M'; \
    echo 'post_max_size=32M'; \
    echo 'expose_php=Off'; \
} > /usr/local/etc/php/conf.d/laravel.ini

WORKDIR /var/www/html

# Copy application source code (respecting .dockerignore)
COPY . .

# Copy production vendor dependencies from composer-builder stage
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from frontend-builder stage
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Setup storage and cache directories with appropriate writable permissions
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install and configure runtime entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose HTTP port
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
