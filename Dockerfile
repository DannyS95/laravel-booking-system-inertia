FROM php:8.3.2RC1-fpm-alpine3.18

RUN docker-php-ext-install bcmath && \
    apk add libzip-dev && \
    docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app/

COPY --from=composer:2.6.6 /usr/bin/composer /usr/bin/composer

RUN composer --version

RUN apk add --update nodejs npm

RUN npm --version && \
    node --version
