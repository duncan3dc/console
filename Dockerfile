ARG PHP_VERSION=7.2
FROM php:${PHP_VERSION}-cli

ARG COVERAGE
RUN if [ "$COVERAGE" = "pcov" ]; then pecl install pcov && docker-php-ext-enable pcov; fi

RUN echo "if [[ $PHP_VERSION == 7.* ]]; then pecl install uopz-6.1.2; else pecl install uopz; fi" > uopz.sh
RUN bash uopz.sh && docker-php-ext-enable uopz

# Install composer to manage PHP dependencies
RUN apt-get update && apt-get install -y git zip
RUN curl https://getcomposer.org/download/1.9.0/composer.phar -o /usr/local/sbin/composer
RUN chmod +x /usr/local/sbin/composer
RUN composer self-update

WORKDIR /app
