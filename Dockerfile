FROM php:7.4-fpm-alpine
# PHP extensions
RUN apk add libxml2-dev
RUN docker-php-ext-install soap
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/
