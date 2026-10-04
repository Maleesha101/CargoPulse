<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Driver
    |--------------------------------------------------------------------------
    |
    | This value serves as the default cache "driver" for your application.
    | Laravel supports various drivers such as "file", "database", "redis",
    | "memcached", "apcu", and "array". You may also specify custom drivers
    | using the "driver" option of the Cache::extend method.
    |
    */

    'default' => env('CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Each driver has its own configuration array. The "default" key above
    | determines which of the "stores" below that are configured are used
    | by the framework and your application. Of course, you are free to
    | add more stores as needed.
    |
    */

    'stores' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
            'prefix' => '',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CONNECTION'),
            'lock_connection' => env('REDIS_LOCK_CONNECTION'),
            'database' => env('REDIS_CACHE_DB', 1),
            'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_').'_',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'duration' => null,
        ],

    ],

];