FROM php:8.1-fpm-alpine

LABEL maintainer="Fail Amir Abdullah, M.Kom."
LABEL description="PHP 8.1-FPM for Loudisland Scalable Backend"

# ─────────────────────────────────────────────
# System dependencies
# ─────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    icu-dev \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    openssl-dev \
    rabbitmq-c-dev \
    autoconf \
    g++ \
    make

# ─────────────────────────────────────────────
# PHP Extensions
# ─────────────────────────────────────────────
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    gd \
    intl \
    opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# AMQP extension (for RabbitMQ)
RUN pecl install amqp && docker-php-ext-enable amqp

# ─────────────────────────────────────────────
# Composer
# ─────────────────────────────────────────────
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ─────────────────────────────────────────────
# App setup
# ─────────────────────────────────────────────
WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
