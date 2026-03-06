FROM php:8.1-cli-alpine

LABEL maintainer="Fail Amir Abdullah, M.Kom."
LABEL description="Background Worker Service - Loudisland Queue Consumer"

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
    oniguruma-dev \
    rabbitmq-c-dev \
    autoconf \
    g++ \
    make

# ─────────────────────────────────────────────
# PHP Extensions (same as app)
# ─────────────────────────────────────────────
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    pcntl \
    bcmath \
    zip \
    intl \
    opcache

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install amqp  && docker-php-ext-enable amqp

# ─────────────────────────────────────────────
# Composer
# ─────────────────────────────────────────────
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# ─────────────────────────────────────────────
# Use redis queue as safe default; switch to rabbitmq after composer update
CMD ["php", "artisan", "queue:work", "redis", "--sleep=3", "--tries=3", "--max-time=3600", "--verbose"]
