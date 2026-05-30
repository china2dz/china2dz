FROM php:8.3-apache
RUN apt-get update && apt-get install -y libpng-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && sed -i 's/^Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/' /etc/apache2/sites-enabled/000-default.conf
COPY . /var/www/html/
CMD ["apache2-foreground"]