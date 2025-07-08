FROM composer AS composer

COPY . /app

WORKDIR /app

RUN composer install --no-dev --no-interaction --ignore-platform-reqs


FROM node:22 AS node

COPY --from=composer /app /app

WORKDIR /app

RUN npm install
RUN npm run build


FROM serversideup/php:8.2-fpm-nginx AS php

COPY --from=node --chown=www-data:www-data /app /var/www/html

COPY --chmod=755 ./deployment/docker/laravel/php/entrypoint.d/ /etc/entrypoint.d/

### Get root access
USER root

### Install Node.js for SSR support
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

### Install php extensions
RUN install-php-extensions mysqli imagick intl pdo
### This script is deprecated and will be removed in a future release!
RUN docker-php-serversideup-s6-init

# Enable OPcache for production performance
ENV PHP_OPCACHE_ENABLE=1

USER www-data

WORKDIR /var/www/html
