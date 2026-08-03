FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=dev

ENV APP_DEBUG=0

RUN composer install --no-dev --optimize-autoloader --no-scripts

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN chown -R www-data:www-data var

EXPOSE 80

CMD ["apache2-foreground"]