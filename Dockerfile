FROM php:8.2-apache

RUN a2enmod rewrite headers && \
    docker-php-ext-install pdo pdo_mysql && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
