# Stage 1 - Install composer dependencies
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --ignore-platform-req=ext-intl

COPY . .


# Stage 2 - PHP runtime
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    bash

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip

WORKDIR /var/www/html

COPY --from=builder /app /var/www/html

# Laravel optimization
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]