ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-cli

ARG COVERAGE
RUN if [ "$COVERAGE" = "pcov" ]; then pecl install pcov && docker-php-ext-enable pcov; fi

RUN apt update && apt install -y git zip
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN echo "phar.readonly = 0" > /usr/local/etc/php/conf.d/console.ini

RUN echo "if [[ $PHP_VERSION == 8.4.* ]]; then echo 'error_reporting = E_ALL ^ E_DEPRECATED' >> /usr/local/etc/php/conf.d/console.ini; fi" > deprecated.sh
RUN bash deprecated.sh

WORKDIR /app
RUN git config --global --add safe.directory /app
