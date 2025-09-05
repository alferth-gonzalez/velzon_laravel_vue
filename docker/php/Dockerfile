FROM php:8.2-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libicu-dev libonig-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath intl gd zip opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Config PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

# Permisos (opcional, depende del host)
RUN usermod -u 1000 www-data || true && groupmod -g 1000 www-data || true
