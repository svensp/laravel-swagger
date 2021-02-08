<?php namespace LaravelSwagger\OpenApi;

/**
 * Interface ApiDocReader
 * @package LaravelSwagger\OpenApi
 */
interface ApiDocIO
{

    public function update(string $path, callable $modifier);
}