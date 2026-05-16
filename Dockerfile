# Use official PHP with Apache
FROM php:8.2-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Explicitly ensure Apache handles PHP files
RUN echo "<FilesMatch \\.php$>\n    SetHandler application/x-httpd-php\n</FilesMatch>" > /etc/apache2/conf-available/php-handling.conf && \
    a2enconf php-handling

# Set DirectoryIndex to index.php
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/docker-php.conf && \
    a2enconf docker-php

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
