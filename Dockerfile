
FROM php:8.2-apache

# Instalar PDO MySQL y habilitar módulos Apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers rewrite

# Copiar los archivos del proyecto
COPY . /var/www/html/

# Configuración de seguridad de Apache
RUN printf '%s\n' \
    '<Directory /var/www/html/config>' \
    'Require all denied' \
    '</Directory>' \
    '<Directory /var/www/html>' \
    'AllowOverride All' \
    '</Directory>' \
    > /etc/apache2/conf-available/cafeteria-security.conf \
    && a2enconf cafeteria-security \
    && chown -R www-data:www-data /var/www/html

# Puerto HTTP de Apache
EXPOSE 80

# Iniciar Apache después de preparar el certificado de Render
CMD ["sh", "-c", "if [ -f /etc/secrets/ca.pem ]; then cp /etc/secrets/ca.pem /tmp/cafeteria-ca.pem && chmod 644 /tmp/cafeteria-ca.pem; fi; exec apache2-foreground"]
