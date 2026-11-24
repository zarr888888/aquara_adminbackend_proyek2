FROM php:8.3-apache-bookworm AS base
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev libzip-dev mariadb-client unzip supervisor && docker-php-ext-install -j2 intl zip pdo_mysql opcache pcntl && rm -rf /var/lib/apt/lists/* && a2enmod rewrite headers
WORKDIR /var/www/html
ENV APP_ENV=production APP_DEBUG=false PORT=8080 LOG_CHANNEL=stderr
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && printf 'upload_max_filesize=12M\npost_max_size=14M\nexpose_php=Off\n' > "$PHP_INI_DIR/conf.d/app.ini"
FROM base AS build
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader
COPY app app
COPY database/migrations database/migrations
COPY database/seeders database/seeders
COPY database/factories database/factories
COPY bootstrap bootstrap
COPY routes routes
COPY config config
COPY lang lang
COPY public public
COPY resources resources
COPY artisan .
RUN composer dump-autoload --no-dev --optimize --no-scripts && composer check-platform-reqs --no-dev
FROM base
COPY --from=build /var/www/html /var/www/html
COPY docker/rc-supervisor.conf /etc/samudera-supervisor.conf
COPY docker/rc-start.sh /usr/local/bin/samudera-start
RUN sed -i 's/\r$//' /usr/local/bin/samudera-start && chmod +x /usr/local/bin/samudera-start && mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8080
CMD ["samudera-start"]