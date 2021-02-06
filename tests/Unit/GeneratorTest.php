<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\OpenApi\Controller;
use LaravelSwagger\OpenApi\DefinedRoute;
use LaravelSwagger\OpenApi\Updater;
use LaravelSwaggerTest\TestCase;

/**
 * Class GeneratorTest
 * @package LaravelSwaggerTest\Unit
 */
class GeneratorTest extends TestCase
{

    private Updater $updater;

    private array $routes;

    public function setUp() : void {
    	parent::setUp();

    	$this->updater = $this->app->make(Updater::class);

    	$this->routes = [];
    }

    /**
     * @test
     */
    public function looks_for_open_api_yaml_specified_in_controller()
    {
        $this->withDefinedRoute(function(DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withController('TestController', function(Controller $controller) {
        });

        $this->updateRoutes();

        $this->assertTrue(true);
    }

    private function withDefinedRoute(\Closure $modifier)
    {
        $route = new DefinedRoute();
        $modifier($route);
        $this->routes[] = $route;
    }

    private function withController(string $controllerClassPath, \Closure $modifier = null)
    {
        $modifier ??= function() {};

        $controller = new Controller();
        $modifier($controller);
    }

    private function updateRoutes()
    {
        $this->updater->update($this->routes);
    }

}