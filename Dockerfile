# Manager Library — API (Laravel 11, PHP 8.3)
# Base image: php:8.3-cli as required by the approved plan (T-01)
FROM php:8.3-cli

# System dependencies for PostgreSQL and image processing
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        gd \
        pcntl \
        bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (production, no dev)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application source
COPY . .

# Run post-install scripts after copying source
RUN composer run-script post-autoload-dump

# Storage directories must be writable
RUN mkdir -p storage/app/public/covers \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /var/www/html/entrypoint.sh

# APP_KEY is generated at startup by entrypoint.sh if not supplied via environment.
# Never write a real key into this file or docker-compose.yml.
EXPOSE 8000

ENTRYPOINT ["/var/www/html/entrypoint.sh"]
