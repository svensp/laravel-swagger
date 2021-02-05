<?php

namespace LaravelSwagger\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

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
     * @return void
     */
    public function handle()
    {
        $routes = $this->getRoutes();

        $this->displayRoutes($routes);
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        return collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return [
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
        ];
    }

    /**
     * Display the route information on the console.
     *
     * @param  array  $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }

}