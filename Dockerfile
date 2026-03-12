FROM php:8.3-apache AS base

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        libxml2-dev \
        libicu-dev \
        libonig-dev \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        mysqli \
        pdo_mysql \
        mbstring \
        xml \
        zip \
        intl \
        exif \
        opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/fleetco.ini"

# Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/fleetco

# Install dependencies first (layer cache)
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application
COPY . .

# Finalise autoloader
RUN composer dump-autoload --no-dev --optimize

# Writable dirs
RUN mkdir -p fleetco/templates_c fleetco/files \
    && chown -R www-data:www-data fleetco/templates_c fleetco/files

EXPOSE 80

# ─── Development stage ────────────────────────────────────────────────────────
FROM base AS dev

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/xdebug.ini "$PHP_INI_DIR/conf.d/xdebug.ini"

RUN composer install --prefer-dist
