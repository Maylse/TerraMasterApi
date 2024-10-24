# Use the official PHP 8.2 image with FPM
FROM php:8.2-fpm

# Set the working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the composer.lock and composer.json files
COPY composer.lock composer.json ./

# Install PHP dependencies using Composer
RUN composer install --no-interaction --prefer-dist

# Copy the rest of your application files
COPY . .

# Set permissions for Laravel storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start the PHP-FPM server
CMD ["php-fpm"]
