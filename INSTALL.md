# How to run in local
1. Run `composer install`
2. copy `.env.example` to `.env` if not done yet
3. Do some replacement on the credential that used.
```
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
4. Run `php artisan migrate`
5. php artisan serve