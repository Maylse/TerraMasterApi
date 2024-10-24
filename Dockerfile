# Use the official PHP image with the required extensions
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Copy the application code
COPY . .

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install required PHP extensions for MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Expose port 9000
EXPOSE 9000

# Command to run the application
CMD ["php-fpm"]
