FROM php:7.4-fpm-alpine
# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
