


## Application Setup Commands
```sh
    composer create-project laravel/laravel app
    cd app
    php artisan install:api
    composer require spatie/laravel-permission
    cloudinary-labs/cloudinary-laravel
    dedoc/scramble

    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan config:clear
    php artisan migrate
    php artisan make:seeder UserRoleSeeder

    composer require laravel/pulse
    php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
    php artisan migrate

```


OtpToken
Activity
Asset
UserProfile


formulate the try and catch as a middleware
// 'error' => $e->getMessage(),
// 'trace' => $e->getTraceAsString(),
// 'request' => request()->method(),
// 'request_data' => request()->all(),
// 'request_url' => request()->url(),
// 'request_ip' => request()->ip(),


## Simple Terminal Commands
````sh

    php artisan make:model Department -mcrR --api
    php artisan make:resource DepartmentResource
    php artisan make:model WarehouseImage -m
```
