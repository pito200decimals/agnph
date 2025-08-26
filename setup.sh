#!/bin/bash

if [ "$1" = "clean" ]; then
    echo Cleaning up AGNPH site!
    # Removing dependencies
    rm -rf html/vendor
		# Removing Custom Data
		rm -r \
			html/uploads \
			html/user/bio \
			html/user/data \
			html/gallery/data \
			html/fics/data \
			html/user/data \
			html/images/uploads \
			html/images/staff \
			html/skin_template_cache
    # Remove database
    docker compose up -d db && sleep 10
    docker compose exec -it db mariadb -uagnph -pagnph -e "DROP DATABASE IF EXISTS agnph; CREATE DATABASE agnph;"
    docker compose down db

    exit 0
fi

echo Setting up AGNPH site!
# Removing Custom Data
rm -r \
	html/uploads \
	html/user/bio \
	html/user/data \
	html/gallery/data \
	html/fics/data \
	html/user/data \
	html/images/uploads \
	html/images/staff \
	html/skin_template_cache
# Bring back custom directories
 mkdir -p \
	html/user/data \
	html/data/bio \
	html/gallery/data \
	html/uploads \
	html/fics/data/chapters \
	html/images/uploads/avatars

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
