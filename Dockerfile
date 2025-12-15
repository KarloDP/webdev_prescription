FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

COPY vhost_config.conf /etc/apache2/sites-available/webdev_prescription.conf

RUN a2ensite webdev_prescription.conf \
    && a2dissite 000-default.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN apachectl -t

EXPOSE 80

CMD ["apache2-foreground"]

