FROM php:7.4-cli
RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install redis-5.2.2 \
    && pecl install memcached-3.1.5 \
    && pecl install apcu \
    && docker-php-ext-enable redis memcached