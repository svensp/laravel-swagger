<?php namespace LaravelSwagger\Controllers;

/**
 * Interface Cache
 * @package LaravelSwagger\Controllers
 */
interface Cache
{

    public function remember($key, callable $create, int $ttlInSeconds = 500);
}
