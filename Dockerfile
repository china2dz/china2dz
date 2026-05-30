FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork
COPY . /var/www/html/
CMD bash -c "sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/*.conf && apache2-foreground"