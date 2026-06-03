FROM php:8.3-fpm

WORKDIR /var/www/app

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    unzip \
    libssl-dev \
    git \
    && docker-php-ext-install pdo_mysql \
    && git clone --depth 1 --branch 6.1.0 https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && docker-php-ext-configure /tmp/phpredis \
    && docker-php-ext-install /tmp/phpredis \
    && rm -rf /tmp/phpredis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer