<?php namespace LaravelSwagger\OpenApi;

/**
 * Class ControllerWithRoutes
 * @package LaravelSwagger\OpenApi
 */
class ControllerWithRoutes
{

    public Controller $controller;

    /**
     * @var DefinedRoute[] $routes
     */
    public array $routes = [];

    public static function fromController(Controller $controller)
    {
        $instance = new self;
        $instance->controller = $controller;
        return $instance;
    }
}
