FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libxml2-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        mysqli \
        gd \
        zip \
        intl \
        mbstring \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN git config --system --add safe.directory /srv

WORKDIR /srv
