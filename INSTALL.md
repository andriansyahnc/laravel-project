# How to run in local
1. Run `composer install`
2. copy `.env.example` to `.env` if not done yet
3. Do some replacement on the credential:
```
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
4. Run `php artisan migrate`
5. php artisan serve
6. please import `kost-api.json` to postman / your insomnia.
7. please run `php artisan schedule:run` to run the scheduler

# How to run unit test in local
1. Run `composer install` if it is your first time.
2. copy `.env.example` to `.env.testing` if not done yet.
3. Do some replacement on the credential:
```
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
4. Run this command
```
vendor/bin/phpunit --stop-on-error --coverage-html builds
```
5. open your browser, and copy the path to the <root>/builds/index.html
6. open file:///path/to/builds/index/html to check coverage.