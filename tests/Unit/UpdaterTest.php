<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\OpenApi\ApiDocIO;
use LaravelSwagger\OpenApi\Controller;
use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\OpenApi\DefinedParameter;
use LaravelSwagger\OpenApi\DefinedRoute;
use LaravelSwagger\OpenApi\FoundRoute;
use LaravelSwagger\OpenApi\NoApiDocSpecifiedException;
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

    public function setUp() : void
    {
        parent::setUp();

        $this->controllerParser = $this->mockAndRegisterInstance(ControllerParser::class);
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
        $this->withRouteAndMatchingController($apiDocPath);

        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdated($apiDocPath);
    }

    /**
     * @test
     * @dataProvider apiDocPaths
     */
    public function only_updates_api_doc_once($apiDocPath)
    {
        $this->withDefinedRoute(function (DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withDefinedRoute(function (DefinedRoute $route) {
            $route->controller = 'TestController';
        });

        $this->withController('TestController', function (Controller $controller) use ($apiDocPath) {
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
        $this->withRouteAndMatchingController($apiDocPath);

        $this->updateAndAssertResult($apiDocPath, [], function ($resultApiDoc) {
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
        $this->withRouteAndMatchingController($apiDocPath);

        $this->updateAndAssertResult($apiDocPath, [
            'openapi' => '1.0.0',
            'info' => [
                'title' => 'titletest',
                'version' => '0.0.1',
            ],
            'new' => 'exists',
            'paths' => [
                '/kekse' => [
                    'post' => [
                        'title' => 'hi'
                    ]
                ]
            ]
        ], function ($resultApiDoc) {
            $this->assertArrayEquals('1.0.0', 'openapi', $resultApiDoc);
            $this->assertArrayEquals('titletest', 'info.title', $resultApiDoc);
            $this->assertArrayEquals('0.0.1', 'info.version', $resultApiDoc);
            $this->assertArrayEquals('hi', 'paths./kekse.post.title', $resultApiDoc);
            $this->assertArrayEquals('exists', 'new', $resultApiDoc);
        });
    }

    /**
     * @test
     * @dataProvider apiDocPathMethods
     * @param $apiDocPath
     */
    public function api_doc_creates_routes_for_all_methods($apiDocPath, $laravelMethodName, $openApiMethodName)
    {
        $this->withRouteAndMatchingController($apiDocPath, function (DefinedRoute $route) use ($laravelMethodName) {
            $route->controller = 'TestController';
            $route->path = '/user/{user_id}';
            $route->parameters = [
                DefinedParameter::fromName('user_id')
            ];
            $route->setMethodFromLaravelName($laravelMethodName);
        });

        $this->updateAndAssertResult($apiDocPath, [], function ($resultApiDoc) use ($openApiMethodName) {
            $this->assertArrayHas("paths./user/{user_id}.{$openApiMethodName}", $resultApiDoc);
            $this->assertArrayEquals(
                'TODO: Summary',
                "paths./user/{user_id}.{$openApiMethodName}.summary",
                $resultApiDoc
            );

            $this->assertArrayEquals(
                'TODO: Description',
                "paths./user/{user_id}.{$openApiMethodName}.parameters.0.description",
                $resultApiDoc
            );
            $this->assertArrayEquals(
                'user_id',
                "paths./user/{user_id}.{$openApiMethodName}.parameters.0.name",
                $resultApiDoc
            );
            $this->assertArrayEquals(
                'path',
                "paths./user/{user_id}.{$openApiMethodName}.parameters.0.in",
                $resultApiDoc
            );
            $this->assertArrayEquals(
                'true',
                "paths./user/{user_id}.{$openApiMethodName}.parameters.0.required",
                $resultApiDoc
            );
        });
    }

    /**
     * @test
     */
    public function ignores_routes_without_apidocs()
    {
        $this->withRouteAndControllerWithoutApiDoc();

        $this->updateWithDefinedRoutes();

        $this->assertNoApiDocUpdate();
    }

    /**
     * @test
     */
    public function controller_without_apidoc_call_hook()
    {
        $this->withRouteAndControllerWithoutApiDoc();

        $called = false;
        $this->updater->onControllerWithoutApidoc(function () use (&$called) {
            $called = true;
        });
        $this->updateWithDefinedRoutes();

        $this->assertTrue($called);
    }


    /**
     * @test
     * @dataProvider  dataNoLongerPresentMethods
     */
    public function routes_which_are_no_longer_present_cause_alert($method, $assertMethod)
    {
        $this->withRouteAndMatchingController('@/api-doc.yml', function (DefinedRoute $route) use ($method) {
            $route->path = '/test';
            $route->setMethodFromLaravelName($method);
        });

        $hookRan = false;
        $this->updater->onUnknownRoute(
            function (string $apiDocPath, FoundRoute $foundRoute) use (&$hookRan, $assertMethod) {
                $this->assertEquals('@/api-doc.yml', $apiDocPath, "api-doc.ylm path did not match");
                $this->assertTrue(
                    $foundRoute->isPath('/does-not-exist'),
                    "Path was not expected /does-not-exist"
                );
                $assertMethod($foundRoute);
                $hookRan = true;
            }
        );

        $this->updateWithDefinedRoutesAndRun('@/api-doc.yml', [
            'paths' => [
                '/test' => [
                    $method => [
                    ]
                ],
                '/does-not-exist' => [
                    $method => [
                    ]
                ]
            ]
        ]);

        $this->assertTrue($hookRan, 'Unknown route hook was not triggered despite one being present');
    }

    public function dataNoLongerPresentMethods()
    {
        return [
            [ 'get', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isGet());
            } ],
            [ 'post', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isPost());
            } ],
            [ 'put', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isPut());
            } ],
            [ 'patch', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isPatch());
            } ],
            [ 'delete', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isDelete());
            } ],
            [ 'options', function (FoundRoute $foundRoute) {
                $this->assertTrue($foundRoute->isOptions());
            } ],
        ];
    }

    private function withRouteAndControllerWithoutApiDoc()
    {
        $this->withDefinedRoute(function (DefinedRoute $route) {
            $route->controller = 'ControllerWithoutApiDoc';
        });

        $this->controllerParser->shouldReceive('parse')->andThrow(new NoApiDocSpecifiedException());
    }

    private function updateAndAssertResult($apiDocPath, array $startData, callable $assertions)
    {
        $this->updateWithDefinedRoutes();

        $this->assertApiDocUpdateResult($apiDocPath, $startData, $assertions);
    }

    private function withRouteAndMatchingController($apiDocPath, callable $modifier = null)
    {
        $controllerClassPath = 'TestController';
        $modifier ??= function () {
        };

        $this->withDefinedRoute(function (DefinedRoute $route) use ($controllerClassPath, $modifier) {
            $route->controller = $controllerClassPath;
            $modifier($route);
        });

        $this->withController($controllerClassPath, function (Controller $controller) use ($apiDocPath) {
            $controller->apiDocPath = $apiDocPath;
        });
    }

    private function withDefinedRoute(\Closure $modifier)
    {
        $controller = $this->faker->randomElement([
            'Controller',
            'Test',
        ]);
        $path = $this->faker->randomElement([
            '@Core/api-docs.yml',
            '@End/api-docs.yml',
            '@Cookies/api-docs.yml',
        ]);
        $route = DefinedRoute::fromControllerAndPath($controller, $path);
        $modifier($route);
        $this->routes[] = $route;
    }

    private function withController(string $controllerClassPath, \Closure $modifier = null)
    {
        $modifier ??= function () {
        };

        $controller = new Controller();
        $controller->path = $controllerClassPath;
        $modifier($controller);
        $this->controllerParser->shouldReceive('parse')->with($controllerClassPath)->andReturn($controller);
    }

    private function updateWithDefinedRoutes()
    {
        $this->updater->update($this->routes);
    }

    private function updateWithDefinedRoutesAndRun($expectedApiDocPath, $startData)
    {
        $this->apiDocReader->shouldReceive('update')
            ->withArgs(function ($apiDocPath, $callable) use ($expectedApiDocPath, $startData) {
                if ($apiDocPath !== $expectedApiDocPath) {
                    return false;
                }

                $resultData = $callable($startData);
                return true;
            });
        $this->updater->update($this->routes);
    }

    private function assertApiDocUpdated(string $docPath)
    {
        $this->apiDocReader->shouldHaveReceived()->update($docPath, callableValue())->once();
    }

    private function assertApiDocUpdateResult(string $expectedApiDocPath, array $startData, callable $assertions)
    {
        $this->executeCallablePassedToApiDocReader($expectedApiDocPath, $startData, $assertions);
    }

    private function executeCallablePassedToApiDocReader(
        string $expectedApiDocPath,
        array $startData,
        callable $do = null
    ) {
        $do ??= function () {
        };

        $this->apiDocReader->shouldHaveReceived('update')
            ->withArgs(function ($apiDocPath, $callable) use ($expectedApiDocPath, $startData, $do) {
                if ($apiDocPath !== $expectedApiDocPath) {
                    return false;
                }

                $resultData = $callable($startData);
                $do($resultData);
                return true;
            });
    }

    private function assertNoApiDocUpdate()
    {
        $this->apiDocReader->shouldNotHaveReceived('update');
    }

    public function apiDocPaths() : array
    {
        return [
            [ '@Core/api-doc.yml' ],
            [ '@cookies/api-doc.yml' ],
            [ '@Module/api-doc.yml' ]
        ];
    }

    public function apiDocPathMethods()
    {
        return [
            [ '@Core/api-doc.yml', 'get', 'get' ],
            [ '@cookies/api-doc.yml', 'post', 'post'  ],
            [ '@Module/api-doc.yml', 'put', 'put'  ],
            [ '@Lib/api-doc.yml', 'patch', 'patch'  ],
            [ '@Vendor/api-doc.yml', 'delete', 'delete'  ],
            [ '@Doink/api-doc.yml', 'options', 'options'  ],
        ];
    }
}
