FROM php:8.2-fpm-alpine

# install dependencies sistem
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev

# konfigurasi gd
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# install extension php
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    tokenizer \
    xml \
    ctype \
    bcmath \
    gd \
    zip \
    opcache

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy composer dulu
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

# copy project
COPY . .

# permission laravel
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# nginx dan supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]