#!/bin/bash

if [ "$1" = "clean" ]; then
    echo Cleaning up AGNPH site!
    # Removing dependencies
    rm -rf html/vendor
    # Remove database
    docker compose up -d db && sleep 10
    docker compose exec -it db mariadb -uagnph -pagnph -e "DROP DATABASE IF EXISTS agnph; CREATE DATABASE agnph;"
    docker compose down db

    exit 0
fi

echo Setting up AGNPH site!

# Composer
echo Downloading Composer Dependencies
docker run -it \
    -v ./html:/app \
    --user $(id -u):$(id -g) \
    composer install

# Database
echo Creating clean database

# Ensure the services are all running
docker compose up -d site db && sleep 10

docker compose exec -it \
    -w /var/www/html/setup \
    site \
    php sql_setup.php

docker compose down

echo -e "All done! Run \033[1mdocker compose up -d\033[0m to start the website!"
