<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\OpenApi\ApiDocIO;
use LaravelSwagger\OpenApi\Controller;
use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\OpenApi\DefinedRoute;
use LaravelSwagger\OpenApi\Updater;
use LaravelSwaggerTest\TestCase;
use Mockery\MockInterface;

/**
 * Class Updater
 * @package LaravelSwaggerTest\Unit
 */
class UpdaterTest extends TestCase
{

    private Updater $updater;

    private array $routes;

    /**
     * @var ControllerParser|MockInterface
     */
    protected $controllerParser;

    /**
     * @var ApiDocIO|\Mockery\MockInterface
     */
    protected $apiDocReader;

    public function setUp() : void {
    	parent::setUp();

    	$this->controllerParser = $this->mockAndRegisterInstance( ControllerParser::class);
    	$this->apiDocReader = $this->mockAndRegisterInstance(ApiDocIO::class);

        $this->updater = $this->app->make(Updater::class);

    	$this->routes = [];
    }

    /**
     * @test
     */
    public function looks_for_open_api_yaml_specified_in_controller()
    {
        $apiDocPath = $this->faker->randomElement([
            '@Core/api-doc.yml',
            '@cookies/api-doc.yml',
            '@Module/api-doc.yml'
        ]);

        $this->withDefinedRoute(function(DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withController('TestController', function(Controller $controller) use ($apiDocPath) {
            $controller->apiDocPath = $apiDocPath;
        });

        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdated($apiDocPath);
    }

    /**
     * @test
     */
    public function only_updates_api_doc_once()
    {
        $apiDocPath = $this->faker->randomElement([
            '@Core/api-doc.yml',
            '@cookies/api-doc.yml',
            '@Module/api-doc.yml'
        ]);

        $this->withDefinedRoute(function(DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withDefinedRoute(function(DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withController('TestController', function(Controller $controller) use ($apiDocPath) {
            $controller->apiDocPath = $apiDocPath;
        });

        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdated($apiDocPath);
    }

    private function withDefinedRoute(\Closure $modifier)
    {
        $route = new DefinedRoute();
        $route->controller = $this->faker->randomElement([
            'Controller',
            'Test',
        ]);
        $route->path = $this->faker->randomElement([
            '@Core/api-docs.yml',
            '@End/api-docs.yml',
            '@Cookies/api-docs.yml',
        ]);
        $modifier($route);
        $this->routes[] = $route;
    }

    private function withController(string $controllerClassPath, \Closure $modifier = null)
    {
        $modifier ??= function() {};

        $controller = new Controller();
        $controller->path = $controllerClassPath;
        $modifier($controller);
        $this->controllerParser->shouldReceive('parse')->with($controllerClassPath)->andReturn($controller);
    }

    private function updateWithDefinedRoutes()
    {
        $this->updater->update($this->routes);
    }

    private function assertApiDocUpdated(string $docPath)
    {
        $this->apiDocReader->shouldHaveReceived()->update($docPath, callableValue() )->once();
    }

}