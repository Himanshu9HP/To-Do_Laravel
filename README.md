
# Laravel ToDo Simple App

This is simple laravel to-do app

This is built on Laravel Framework 9. 

## Installation

Clone the repository-
```
https://github.com/Himanshu9HP/To-Do_Laravel.git
```

Then cd into the folder with this command-
```
cd To-Do_Laravel
```

Then do a composer install and update
```
composer install
composer update
```

Then create a environment file using this command-
```
cp .env.example .env
```

Then run key genrate command
```
php artisan key:generate
```

Then edit `.env` file with  credential for  database server.
Then create a database named `to_do` and then do a database migration using this command-
```
php artisan migrate
```


## Run server

Run server using this command-
```
php artisan serve
```

