<?php

namespace LaravelSwagger\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LaravelSwagger\OpenApi\DefinedRoute;
use LaravelSwagger\OpenApi\FoundRoute;
use LaravelSwagger\OpenApi\Updater;

/**
 * Class GenerateOpenApiCommand
 */
class GenerateOpenApiCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'open-api:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate api-docs.json from laravel route definitions';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    private array $skippedControllers = [];

    /**
     * Create a new route command instance.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @param Updater $updater
     */
    public function handle(Updater $updater)
    {
        $routes = $this->getRoutes();

        $updater->onControllerWithoutApidoc(function (DefinedRoute $definedRoute) {
            $this->warnSkippedNoApidoc($definedRoute);
        });
        $updater->onUnknownRoute(function (string $apiDocPath, FoundRoute $foundRoute) {
            $this->warnUnknownRoute($apiDocPath, $foundRoute);
        });
        $updater->update($routes);
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return DefinedRoute[]
     */
    protected function getRoutes() : array
    {
        return collect($this->router->getRoutes())
            ->filter(function ($route) {
                return $this->isControllerRoute($route);
            })->map(function ($route) {
                return $this->getRouteInformations($route);
            })->flatten()->all();
    }

    private function isControllerRoute(Route $route) : bool
    {
        return array_key_exists('controller', $route->action);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformations(Route $route) : array
    {
        $controller = $this->parseController($route);

        return collect($route->methods())->map(function ($laravelMethodName) use ($controller, $route) {
            return DefinedRoute::fromControllerAndPath($controller, $route->uri())
                ->setMethodFromLaravelName($laravelMethodName);
        })->all();
    }

    private function parseController(Route $route)
    {
        list($controller) = explode('@', $route->action['controller']);
        return $controller;
    }

    private function warnSkippedNoApidoc(DefinedRoute $definedRoute)
    {
        $controller = $definedRoute->controller;
        if ($this->hasWarnedForController($controller)) {
            return;
        }

        $this->warn("Skipped {$definedRoute->controller} - no apidoc defined");
        $this->rememberWarnedForController($controller);
    }

    private function warnUnknownRoute(string $apiDocPath, FoundRoute $route)
    {
        $upperCaseMethodName = strtoupper($route->getOpenApiMethodName());
        $this->warn(
            "Route no longer present:"
            ." {$apiDocPath} - {$upperCaseMethodName} {$route->getPath()}"
        );
    }

    private function hasWarnedForController($controller) : bool
    {
        return array_key_exists($controller, $this->skippedControllers) ;
    }

    private function rememberWarnedForController(string $controller)
    {
        $this->skippedControllers[$controller] = true;
    }
}
