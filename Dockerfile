FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2dismod mpm_event && a2enmod mpm_prefork

RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/80/${PORT}/g' /etc/apache2/sites-enabled/000-default.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
