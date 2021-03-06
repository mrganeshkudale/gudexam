FROM php:8.0-apache

RUN apt-get update && apt-get install -y \
        libpng-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        zip \
        curl \
        unzip \
        git \
        nano \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-source delete

COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite  

RUN service apache2 restart

COPY . /var/www/html/

RUN composer install

RUN chown -R $USER:www-data storage \
&& chown -R $USER:www-data bootstrap/cache \
&& chmod -R 775 storage \
&& chmod -R 775 bootstrap/cache \
&& cd public \
&& chmod -R 775 assets static index.html \
&& cd assets \
&& chmod -R 777 images \
&& cd .. \
&& chown www-data:$USER assets/ static/ index.html \
&& chmod -R 777 data \
&& cd .. \
&& php artisan optimize