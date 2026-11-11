FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql && a2enmod headers rewrite
COPY . /var/www/html/
RUN printf '%s\n' '<Directory /var/www/html/config>' 'Require all denied' '</Directory>' '<Directory /var/www/html>' 'AllowOverride All' '</Directory>' > /etc/apache2/conf-available/cafeteria-security.conf \
    && a2enconf cafeteria-security \
    && chown -R www-data:www-data /var/www/html
EXPOSE 80
