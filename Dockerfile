FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# mod_php requires mpm_prefork; disable mpm_event to avoid "More than one MPM loaded" error
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Allow .htaccess overrides in the document root
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy all project files into the web root
COPY . /var/www/html/

# Set correct ownership for the image upload directories
RUN chown -R www-data:www-data /var/www/html/imgs \
    && chmod -R 755 /var/www/html/imgs

# Use entrypoint script to set Apache port from Railway's $PORT env var
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]