FROM php:8.4-cli

RUN pecl install pcov && docker-php-ext-enable pcov

ADD *.phar ./
