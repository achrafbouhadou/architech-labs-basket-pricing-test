FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libzip-dev \
        pkg-config \
        autoconf \
        make \
        gcc \
    && printf "\n" | pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get purge -y --auto-remove pkg-config autoconf make gcc \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV COMPOSER_CACHE_DIR=/tmp/composer-cache \
    XDEBUG_MODE=off

VOLUME ["/app", "/tmp/composer-cache"]

CMD ["php", "-v"]
