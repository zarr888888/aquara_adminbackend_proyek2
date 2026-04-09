FROM php:8.2-fpm

# install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev

# install php extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
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

# nginx & supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]