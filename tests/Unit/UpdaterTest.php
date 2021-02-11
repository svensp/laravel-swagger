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
     * @dataProvider apiDocPaths
     * @param $apiDocPath
     */
    public function looks_for_open_api_yaml_specified_in_controller($apiDocPath)
    {
        $this->withRouteAndMatchingController('TestController', $apiDocPath);

        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdated($apiDocPath);
    }

    /**
     * @test
     * @dataProvider apiDocPaths
     */
    public function only_updates_api_doc_once($apiDocPath)
    {
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

    /**
     * @test
     * @dataProvider apiDocPaths
     * @param $apiDocPath
     */
    public function empty_api_doc_json_creates_file_from_template($apiDocPath)
    {
        $this->updateAndAssertResult($apiDocPath, [], function($resultApiDoc) {
            $this->assertArrayHas('openapi', $resultApiDoc);
            $this->assertArrayHas('info.title', $resultApiDoc);
            $this->assertArrayHas('info.version', $resultApiDoc);
            $this->assertArrayHas('paths', $resultApiDoc);
        });
    }

    /**
     * @test
     * @dataProvider apiDocPaths
     * @param $apiDocPath
     */
    public function api_doc_json_keeps_existing_values($apiDocPath)
    {
        $this->updateAndAssertResult($apiDocPath, [
            'openapi' => '1.0.0',
            'info' => [
                'title' => 'titletest',
                'version' => '0.0.1',
            ],
            'paths' => [
                '/kekse' => [
                    'post' => [
                        'title' => 'hi'
                    ]
                ]
            ]
        ], function($resultApiDoc) {
            $this->assertArrayEquals('1.0.0', 'openapi', $resultApiDoc);
            $this->assertArrayEquals('titletest', 'info.title', $resultApiDoc);
            $this->assertArrayEquals('0.0.1', 'info.version', $resultApiDoc);
            $this->assertArrayEquals('hi', 'paths./kekse.post.title', $resultApiDoc);
        });
    }

    private function updateAndAssertResult($apiDocPath, array $startData, callable $assertions)
    {
        $this->withRouteAndMatchingController('TestController', $apiDocPath);

        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdateResult($apiDocPath, $startData, $assertions);

    }

    private function withRouteAndMatchingController($controllerClassPath, $apiDocPath)
    {
        $this->withDefinedRoute(function(DefinedRoute $route) use ($controllerClassPath) {
            $route->controller = $controllerClassPath;
        });

        $this->withController($controllerClassPath, function(Controller $controller) use ($apiDocPath) {
            $controller->apiDocPath = $apiDocPath;
        });


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

    private function assertApiDocUpdateResult($expectedApiDocPath, array $startData, callable $assertions)
    {
        $this->apiDocReader->shouldHaveReceived('update')
            ->withArgs(function($apiDocPath, $callable) use ($expectedApiDocPath, $startData, $assertions) {
            if($apiDocPath !== $expectedApiDocPath) {
                return false;
            }

            $resultData = $callable($startData);
            $assertions($resultData);
            return true;
        });
    }

    public function apiDocPaths() : array
    {
        return [
            [ '@Core/api-doc.yml' ],
            [ '@cookies/api-doc.yml' ],
            [ '@Module/api-doc.yml' ]
        ];

    }


}