FROM php:8.1-apache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install pdo_mysql extension
RUN docker-php-ext-install pdo_mysql

# Enable mod_rewrite for .htaccess
RUN a2enmod rewrite

# Copy application code
COPY . /var/www/html/

# Install PHP dependencies
RUN composer install --working-dir=/var/www/html

# Allow .htaccess overrides in the document root
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
