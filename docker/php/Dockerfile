# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4.22
ARG COMPOSER_VERSION=2.8
ARG NODE_IMAGE=node:24-bookworm-slim
ARG NGINX_IMAGE=nginx:1.30.2-alpine3.23

FROM composer:${COMPOSER_VERSION} AS composer-bin

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

ARG APP_UID=1000
ARG APP_GID=1000

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/var/www/html/vendor/bin:${PATH}"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        git \
        libfcgi-bin \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
        zip \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY docker/php/conf.d/99-post-md.ini /usr/local/etc/php/conf.d/99-post-md.ini
COPY docker/php/php-fpm.d/zz-post-md.conf /usr/local/etc/php-fpm.d/zz-post-md.conf
COPY docker/php/php-fpm-healthcheck /usr/local/bin/php-fpm-healthcheck

RUN chmod +x /usr/local/bin/php-fpm-healthcheck \
    && groupmod --gid "${APP_GID}" www-data \
    && usermod --uid "${APP_UID}" --gid "${APP_GID}" www-data \
    && mkdir -p /var/www/html /tmp/composer-cache \
    && chown -R www-data:www-data /var/www/html /tmp/composer-cache

WORKDIR /var/www/html

FROM php-base AS development

USER www-data

CMD ["php-fpm"]

FROM php-base AS vendor-production

COPY src/ /var/www/html/

RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --optimize-autoloader \
    && php artisan package:discover --ansi \
    && rm -rf /root/.composer/cache

FROM ${NODE_IMAGE} AS assets-production

WORKDIR /app

COPY src/package.json src/package-lock.json ./
RUN npm ci --ignore-scripts

COPY src/resources ./resources
COPY src/public ./public
COPY src/vite.config.js ./
RUN npm run build

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

COPY --from=vendor-production --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets-production --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN mkdir -p \
      storage/app/public \
      storage/framework/cache/data \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]

FROM ${NGINX_IMAGE} AS web-production

WORKDIR /var/www/html

COPY docker/nginx/default.prod.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app/public \
    && ln -sfn ../storage/app/public /var/www/html/public/storage

CMD ["nginx", "-g", "daemon off;"]
