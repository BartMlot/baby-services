FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    openssl \
    rabbitmq-c \
    rabbitmq-c-dev \
    linux-headers \
    $PHPIZE_DEPS \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apk del --no-cache $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
