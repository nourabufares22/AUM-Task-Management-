FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends curl unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /app
WORKDIR /app

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080
CMD php -S 0.0.0.0:${PORT:-8080}
