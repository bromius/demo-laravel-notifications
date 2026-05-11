FROM php:8.3-fpm-bookworm

ARG UID=1000
ARG GID=1000

RUN groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" -g www-data www-data

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        curl \
        git \
        unzip \
        zip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        default-mysql-client \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]
