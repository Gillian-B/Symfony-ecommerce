# T-WEB-600-LYN-6-1-ecommerce-raphael.olivon
file .env --> set db_user / db_password
``` bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

php bin/console server:start
```
