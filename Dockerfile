# Use the official PHP image with Apache
FROM php:8.1-apache

# Install any necessary PHP extensions (add more as needed)
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy your application files to the container
COPY . .

# Set ownership and permissions
RUN chown -R www-data:www-data /var/www/html/public && \
    chmod -R 755 /var/www/html/public

# Expose port 80
EXPOSE 80
