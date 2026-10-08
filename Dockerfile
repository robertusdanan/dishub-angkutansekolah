FROM php:8.4-cli-alpine

RUN apk add --no-cache curl ca-certificates \
    && curl -sSL -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions gd exif zip pdo_sqlite pdo_mysql opcache

WORKDIR /app

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
