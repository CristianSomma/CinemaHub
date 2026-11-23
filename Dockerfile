# Argomenti condivisi dai due target del Dockerfile.
ARG PHP_VERSION=8.5
ARG APACHE_VERSION=2.4

# Servizio PHP-FPM: esegue gli script PHP e si collega a MariaDB.
FROM php:${PHP_VERSION}-fpm AS php

# mbstring è necessario per mb_strlen() usato dalle validazioni PHP.
# mysqli e pdo_mysql permettono a PHP di collegarsi a MariaDB.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libonig-dev; \
    docker-php-ext-install -j"$(nproc)" mbstring mysqli pdo_mysql; \
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Servizio Apache: riceve le richieste HTTP e inoltra i file PHP a PHP-FPM.
FROM httpd:${APACHE_VERSION} AS web

# Attiva i moduli proxy/proxy_fcgi per comunicare con PHP-FPM,
# rewrite per eventuali regole .htaccess e il virtual host del progetto.
RUN set -eux; \
    sed -i \
        -e 's/^#LoadModule proxy_module /LoadModule proxy_module /' \
        -e 's/^#LoadModule proxy_fcgi_module /LoadModule proxy_fcgi_module /' \
        -e 's/^#LoadModule rewrite_module /LoadModule rewrite_module /' \
        -e 's|^#Include conf/extra/httpd-vhosts.conf|Include conf/extra/httpd-vhosts.conf|' \
        /usr/local/apache2/conf/httpd.conf; \
    grep -q '^LoadModule proxy_module ' /usr/local/apache2/conf/httpd.conf; \
    grep -q '^LoadModule proxy_fcgi_module ' /usr/local/apache2/conf/httpd.conf; \
    grep -q '^Include conf/extra/httpd-vhosts.conf' /usr/local/apache2/conf/httpd.conf

COPY src/docker/apache/default.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf
