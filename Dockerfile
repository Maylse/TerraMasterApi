# Use the official PHP image with Apache
FROM php:8.2-apache

# Install necessary system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy composer.lock and composer.json first to leverage Docker cache
COPY composer.lock composer.json ./

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version  # Check Composer version to confirm installation

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Copy the rest of your Laravel application (this step should include artisan file)
COPY . .

# Ensure the .env file exists and is copied
COPY .env .env

# Set the correct permissions for storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Run artisan commands to clear cache and optimize
RUN php artisan config:cache && php artisan route:cache && php artisan optimize:clear

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
