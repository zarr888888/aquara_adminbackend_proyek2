# ---------- Stage 1 : Build dependencies ----------
FROM composer:2 AS builder

WORKDIR /app

# Install dependency untuk ext-intl
RUN apt-get update && apt-get install -y \
    libicu-dev \
    zip \
    unzip

# Enable intl extension
RUN docker-php-ext-install intl

# Copy composer files
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

# ---------- Stage 2 : Runtime ----------
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    libpng \
    libzip \
    icu-libs \
    oniguruma

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip

WORKDIR /var/www/html

# Copy hasil build
COPY --from=builder /app /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]