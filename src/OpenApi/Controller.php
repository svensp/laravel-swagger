<?php namespace LaravelSwagger\OpenApi;

/**
 * Class Controller
 * @package LaravelSwagger\OpenApi
 */
class Controller
{

    public string $path = '';

    public string $apiDocPath = '';

    public array $tags = [];

    public function getKey()
    {
        return $this->path;
    }
}
