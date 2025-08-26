# agnph

### Development Environment Setup

Simply run

```bash
./setup
```

Then setup the config in `html/includes/config.php` with

```php
$dbhost = "db";
$dbuser = "root";
$dbpass = "root";
$dbname = "agnph";
```

And `html/includes/constants.php`

```php
define("SITE_DOMAIN", "http://localhost:8000");
```

Finally, simply run

```bash
docker compose up -d
```

to deploy a local instance of the website. The site can be found at `http://localhost:8000`, and the mailcatcher site is at `http://localhost:8001`

#### Cleaning

Simply run

```bash
./setup clean
```

to remove any generated content that was created while using the site

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
