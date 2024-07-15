FROM php:8.3-apache-bookworm

RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

EXPOSE 80/tcp
