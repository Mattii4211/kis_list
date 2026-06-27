FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    # --no-scripts
COPY . ./
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var
RUN composer dump-autoload --optimize --classmap-authoritative
RUN a2enmod rewrite
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri 's!<Directory /var/www/html>!<Directory /var/www/html/public>!g' /etc/apache2/sites-available/*.conf || true
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]