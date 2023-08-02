ARG PHP_EXTENSIONS="mysqli pgsql pdo_mysql pdo_pgsql"

FROM thecodingmachine/php:8.2-v4-apache-node18

COPY --chown=docker:docker sources/web .

RUN composer install

WORKDIR /workspace