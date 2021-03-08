<?php namespace LaravelSwagger\Controllers;

/**
 * Interface Cache
 * @package LaravelSwagger\Controllers
 */
interface Cache
{

    public function remember($key, callable $create, int $ttlInSeconds = 500);

    public function set($key, $value, int $ttlInSeconds = 500);
}
