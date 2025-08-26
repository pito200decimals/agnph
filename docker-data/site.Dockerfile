FROM php:8.3-apache

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions mysqli gd && a2enmod rewrite

RUN apt-get update && apt-get install -y msmtp
RUN apt-get clean && \
  rm -rf /var/lib/apt/lists/*
