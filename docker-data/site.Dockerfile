FROM php:8.3-apache

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y msmtp libonig-dev
RUN apt-get clean && \
  rm -rf /var/lib/apt/lists/*

RUN install-php-extensions mbstring mysqli gd && a2enmod rewrite
