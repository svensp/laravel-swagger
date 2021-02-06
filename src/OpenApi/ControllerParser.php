<?php namespace LaravelSwagger\OpenApi;

/**
 * Interface ControllerParser
 * @package LaravelSwagger\OpenApi
 */
interface ControllerParser
{

    public function parse(string $classPath) : Controller;

}