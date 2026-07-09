# ── Stage 1: Composer dependencies ──────────────────────────────────────────
FROM composer:2.7 AS composer_stage

WORKDIR /app

# Copy only what's needed to resolve dependencies
COPY composer.json composer.lock ./

# Install production dependencies only (no dev)
# --ignore-platform-reqs: the composer stage image doesn't have all PHP extensions
# (intl, gd, zip, etc.) — they exist in the final php:8.2-apache runtime image.
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ── Stage 2: Final runtime image ─────────────────────────────────────────────
FROM php:8.2-apache

# Install required PHP extensions for CodeIgniter 4 + MySQL
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        libicu-dev \
        zip \
        unzip \
        git \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        intl \
        mbstring \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (required for CodeIgniter routing)
RUN a2enmod rewrite

# Configure Apache to point to /var/www/html/public (CodeIgniter's public dir)
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy vendor from composer stage
COPY --from=composer_stage /app/vendor ./vendor

# Set correct permissions for writable directory (CI4 needs write access here)
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Copy PHP config for production optimizations
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80

CMD ["apache2-foreground"]
