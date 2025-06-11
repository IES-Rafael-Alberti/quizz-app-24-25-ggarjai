FROM php:8.3-apache

# // MOD instalar libsqlite3-dev antes de compilar la extensión
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite
# // END MOD

# Activar mod_rewrite por si lo necesitas luego
RUN a2enmod rewrite

WORKDIR /var/www/html