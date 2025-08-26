# agnph

## TODOS

This repo is not at parity with its production state, with bugs present.

* SQL query failure when publishing fic
* Check on gallery publishing
* Oekaki fails to load
* Check on admin controls

### Development Environment Setup

Simply run

```bash
./setup
```

Then run

```bash
docker compose up -d
```

to deploy a local instance of the website. The site can be found at `http://localhost:8000`, and the mailcatcher site is at `http://localhost:8001`

### Manual Setup

Start by downloading the site's libraries with `composer`, to do this with docker:

```bash
docker run -it -v ./html:/app --user $(id -u):$(id -g) composer install
```

After, you will need to have to initalize the site's database by running `html/setup/sql_setup.php`. As an example, doing this for the development environment, do:

```bash
docker compose up -d site db
docker compose exec -it -w /var/www/html/setup site php sql_setup.php
```

That's all! The site is now ready for deployment!

### Database Auth

Enter database authentication information into the file located at `html/includes/config.php`

**DO NOT** commit these changes to the GitHub repository.

**FOR DEVELOPMENT**, use the settings:

```php
$dbhost = "db";
$dbuser = "root";
$dbpass = "root";
$dbname = "agnph";
```
