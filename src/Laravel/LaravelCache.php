<?php namespace LaravelSwagger\Laravel;

use LaravelSwagger\Controllers\Cache;

/**
 * Class LaravelCache
 * @package LaravelSwagger\Laravel
 */
class LaravelCache implements Cache
{

    public function remember($key, callable $create, int $ttlInseconds = 500)
    {
        return \Illuminate\Support\Facades\Cache::remember($key, $ttlInseconds, $create);
    }

    public function set($key, $value, int $ttlInSeconds = 500)
    {
        \Illuminate\Support\Facades\Cache::put($key, $value, $ttlInSeconds);
    }
}
