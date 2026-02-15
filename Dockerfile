# =========================
# 1) PHP base + composer deps (vendor)
# =========================
FROM php:8.3-fpm AS phpbase

RUN apt-get update && apt-get install -y \
    git unzip \
    libzip-dev libicu-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip intl gd pcntl exif \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .


# Create Laravel storage structure to satisfy auto-discovery
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader


# =========================
# 2) BUILD FRONTEND (VITE)
# =========================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY --from=phpbase /app /app

RUN npm ci
RUN npm run build


# =========================
# 3) FINAL RUNTIME
# =========================
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev libicu-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    imagemagick libmagickwand-dev \
    libavif-bin \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip intl gd pcntl exif \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .
COPY --from=phpbase /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build


# Create storage directory structure in final image
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

RUN chown -R www-data:www-data storage bootstrap/cache
