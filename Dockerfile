# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update -y && apt-get install -y `
    libpng-dev `
    libjpeg-dev `
    libfreetype6-dev `
    libzip-dev `
    unzip `
    git `
    && docker-php-ext-configure gd --with-freetype --with-jpeg `
    && docker-php-ext-install gd zip

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Set the working directory for the application
WORKDIR /var/www/html

# Copy the composer.json and composer.lock files first to utilize Docker caching
COPY composer.json composer.lock ./

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install the PHP dependencies without running scripts
RUN composer install --no-autoloader --no-scripts

# Copy the rest of your application code into the container
COPY . .

# Generate the optimized autoload files
RUN composer dump-autoload --optimize

# Set permissions for storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy the environment file and generate the application key
COPY .env.example .env  # You may want to rename this to .env if needed
RUN php artisan key:generate

# Expose port 80 for web traffic
EXPOSE 80

# Start the Apache server in the foreground
CMD ["apache2-foreground"]
