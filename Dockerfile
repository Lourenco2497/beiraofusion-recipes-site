FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Remove ALL MPM symlinks then enable only mpm_prefork (required by mod_php)
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Allow .htaccess overrides in the document root
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy all project files into the web root
COPY . /var/www/html/

# Set correct ownership for the image upload directories
RUN chown -R www-data:www-data /var/www/html/imgs \
    && chmod -R 755 /var/www/html/imgs

EXPOSE 80

# Railway sets $PORT dynamically — update Apache to listen on it at startup
CMD PORT="${PORT:-80}" && \
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf && \
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground