FROM php:8.2-apache

# Install mysqli extension for database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions (important for Render/containers)
RUN chown -R www-data:www-data /var/www/html

# Configure Apache DocumentRoot to point to current directory
# (Default is already /var/www/html)

# Expose port 80
EXPOSE 80
