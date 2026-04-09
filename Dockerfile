FROM composer:2 AS builder

WORKDIR /app

# Copy file composer dulu (agar cache Docker bekerja)
COPY composer.json composer.lock ./

# Install dependency Laravel tanpa dev
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --ignore-platform-req=ext-intl

# Copy seluruh project
COPY . .

FROM php:8.3-fpm-alpine

# Install library yang dibutuhkan Laravel
RUN apk add --no-cache \
    libpng \
    libzip \
    icu-libs \
    oniguruma \
    bash

# Install extension PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip

WORKDIR /var/www/html

# Copy hasil build dari stage builder
COPY --from=builder /app /var/www/html

# Permission Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Port aplikasi
EXPOSE 8000

# Jalankan Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]