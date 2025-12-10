FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo_mysql
RUN a2enmod rewrite

WORKDIR /var/www/html

# Vhost matching your config, but using container path
RUN echo '<VirtualHost *:80>\n\
    ServerName webdev-prescription.bytebusters\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options +Indexes +Includes +FollowSymLinks +MultiViews\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/webdev_prescription.conf \
    && a2ensite webdev_prescription \
    && a2dissite 000-default

# Your project root *is* the build context, so copy everything there
COPY . /var/www/html
