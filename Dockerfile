FROM php:8.2-fpm

# Install dependency sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev

# Install extension PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy semua project
COPY . .

# Tambahkan memory unlimited supaya composer tidak gagal
ENV COMPOSER_MEMORY_LIMIT=-1

# Install dependency Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permission Laravel
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000