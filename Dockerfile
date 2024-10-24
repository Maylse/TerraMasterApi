# Use the official PHP image with required extensions
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies and Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install required PHP extensions for MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copy the application code
COPY . .

# Install project dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000
EXPOSE 9000

# Command to run the application
CMD ["php-fpm"]
