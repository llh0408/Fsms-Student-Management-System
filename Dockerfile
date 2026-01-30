FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql

COPY . .

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/htdocs/uploads

EXPOSE 9000

CMD ["php-fpm"]
