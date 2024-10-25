# Use the official PHP image with the required version
FROM php:8.2-fpm

# Install system dependencies and the MongoDB extension
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy the .env file
COPY .env .env

# Generate the application key
RUN php artisan key:generate

EXPOSE 10000

# Start the PHP FastCGI Process Manager
CMD ["php-fpm"]
