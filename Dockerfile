# LACMS - PHP 8 + Apache
FROM php:8.2-apache

# PDO MySQL is the only extra extension needed (curl, mbstring, etc. are already built in)
RUN docker-php-ext-install pdo_mysql

# PHP settings: match the app's 10 MB upload limit and timezone
RUN { \
      echo 'upload_max_filesize=10M'; \
      echo 'post_max_size=12M'; \
      echo 'date.timezone=Asia/Manila'; \
    } > /usr/local/etc/php/conf.d/lacms.ini

# Behind Hostforge's reverse proxy the app must know the original request was HTTPS
RUN printf 'ServerName localhost\nSetEnvIf X-Forwarded-Proto "https" HTTPS=on\n' \
      > /etc/apache2/conf-available/lacms-proxy.conf \
    && a2enconf lacms-proxy

# Listen on the port the platform gives us via $PORT (Hostforge health-checks a
# dynamic port such as 51541). If PORT is not provided, fall back to 80.
ENV PORT=80
RUN sed -ri 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
    && apache2ctl -t

WORKDIR /var/www/html
COPY . /var/www/html/

# Folders the app writes to
RUN mkdir -p logs assets/uploads \
    && chown -R www-data:www-data logs assets/uploads

EXPOSE 80
