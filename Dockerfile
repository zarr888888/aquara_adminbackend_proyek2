FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --ignore-platform-req=ext-intl

COPY . .

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    libpng \
    libzip \
    icu-libs \
    oniguruma \
    bash

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip

WORKDIR /var/www/html

COPY --from=builder /app /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]