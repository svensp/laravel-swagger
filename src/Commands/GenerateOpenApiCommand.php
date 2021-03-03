<?php

namespace LaravelSwagger\Commands;

use Illuminate\Console\Command;
use LaravelSwagger\Laravel\LaravelRouteParser;
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

    private array $skippedControllers = [];

    /**
     * @var LaravelRouteParser
     */
    private LaravelRouteParser $laravelRouteParser;

    /**
     * Create a new route command instance.
     *
     * @param LaravelRouteParser $laravelRouteParser
     */
    public function __construct(LaravelRouteParser $laravelRouteParser)
    {
        parent::__construct();
        $this->laravelRouteParser = $laravelRouteParser;
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
        return $this->laravelRouteParser->toDefinedRoutes();
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
