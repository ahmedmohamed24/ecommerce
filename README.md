# API for  E-commerce

## Technologies:
<p align="center">
    <a href="#">
        <img src="https://img.shields.io/badge/-PHP-f5f5f5?style=for-the-badge&amp;labelColor=grey&amp;logo=PHP&amp;logoColor=white" alt="PHP" style="max-width:100%;">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/-MYSQL-075b9a?style=for-the-badge&amp;labelColor=black&amp;logo=Mysql&amp;logoColor=white" alt="MYSQL" style="max-width:100%;">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/-Docker-61dafb?style=for-the-badge&amp;labelColor=black&amp;logo=docker&amp;logoColor=61dafb" alt="docker" style="max-width:100%;">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/-Postman-F88C00?style=for-the-badge&amp;labelColor=black&amp;logo=postman&amp;logoColor=F88C00" alt="postman" style="max-width:100%;">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/-REDIS-f5f5f5?style=for-the-badge&amp;labelColor=red&amp;logo=redis&amp;logoColor=white" alt="PHP" style="max-width:100%;">
    </a>
</p>

## Features
- ` TDD `
- ` Docker `
- ` Admin | Merchant | User ` basic operations
    1. **Login and Register** Admin only login
    1. **Reset Password**
    1. **verify Email** only user and Merchant
    1. **OTP** to verify Phone Number **TWILLIO Service**
    1. **CRUD operations** based on the responsiblities
- ` PayPal ` Payment Intergration
- ` STRIPE ` Payment Intergration
- ` Redis ` cache server
- ` Algolia ` Elastic search
- ` SHOPPING Cart ` in the case of using session (front-end application)
- ` FACEBOOK & GITHUB ` Authentication
- ` POSTMAN ` automation testing
- ` Malitrap ` for mailing service in production
- ` MailTrap ` for mailing service in Testing
-  ` Queues and Jobs ` (Redis Driver)
-  `Github Actions`

## Getting started:
1. Fork this Repository
1. change the current directory to project path
   ex: ```cd ecommerce ```
1. make the database folder ```mkdir mysql```
1. ``` docker-compose build && docker-compose up -d ```

    **alert:** </span> if there is a server running in your machine, you should stop it or change port 80 in docker-compose.yml to another port(8000)

1. install dependencies with composer ```cd src && composer install```, if you are in a production server and composer is not installed, you can install the dependencies from docker environment ``` docker-compose exec php /bin/sh``` then, ```composer install```
1. run ``` docker-compose exec php php /var/www/html/artisan migrate --seed```
1. run ``` docker-compose exec php php /var/www/html/artisan test``` to run all tests and make sure everything is OK
1. run ``` docker-compose exec php php /var/www/html/artisan queue:work redis --tries=2``` to start the Queue
1. import the database in POSTMAN and begin your work


**Info:** if you want only the Laravel project,
copy the  **/src** folder to wherever you want and  make database with name **store** , then generate key
```php artisan key:generate```, then ``` php artisan serve ```

