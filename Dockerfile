FROM composer:2 AS builder

WORKDIR /app

# Copy composer file
COPY composer.json composer.lock ./

# Install dependency tanpa dev
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# Copy seluruh project
COPY . .

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

FROM php:8.3-fpm-alpine

# Install library yang dibutuhkan
RUN apk add --no-cache \
    libpng \
    libzip \
    icu-libs \
    oniguruma

# Install extension PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip

WORKDIR /var/www/html

# Copy vendor dari stage builder
COPY --from=builder /app /var/www/html

# Permission Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]