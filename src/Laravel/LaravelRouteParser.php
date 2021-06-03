<?php namespace LaravelSwagger\Laravel;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use LaravelSwagger\OpenApi\DefinedParameter;
use LaravelSwagger\OpenApi\DefinedRoute;

/**
 * Class LaravelRouteParser
 * @package LaravelSwagger\Laravel
 */
class LaravelRouteParser
{
    /**
     * @var Router
     */
    private Router $router;

    private bool $ignoreHeadRoutes = false;


    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function toDefinedRoutes()
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
        $controller = $this->parseControllerClassPath($route);
        $parameters = $this->parseParameters($route);

        return collect($route->methods())
            ->filter(function ($laravelMethodName) {
                if ($this->isHeadMethod($laravelMethodName) && $this->ignoreHeadRoutes) {
                    return false;
                }

                return true;
            })
            ->map(function ($laravelMethodName) use ($controller, $route, $parameters) {
                $routeName = $this->addHeadToHeadMethodRouteName(
                    $laravelMethodName,
                    $route->getName() ?? ''
                );

                return DefinedRoute::fromControllerAndPath($controller, $route->uri())
                ->setMethodFromLaravelName($laravelMethodName)
                ->setParameters($parameters)
                ->setName($routeName);
            })->all();
    }

    private function addHeadToHeadMethodRouteName($methodName, $routeName)
    {
        if ($this->isHeadMethod($methodName)) {
            return $routeName.'Head';
        }

        return $routeName;
    }

    private function isHeadMethod($methodName)
    {
        return strtolower($methodName) === 'head';
    }

    private function parseControllerClassPath(Route $route): string
    {
        list($controller) = explode('@', $route->action['controller']);
        return $controller;
    }

    private function parseParameters(Route $route)
    {
        $path = $route->uri();

        $matches = [];
        preg_match_all('~\{([^}]+)\}~', $path, $matches);

        $parameters = [];

        foreach (Arr::get($matches, 1, []) as $parameterName) {
            $parameters[] = DefinedParameter::fromName($parameterName);
        }

        return $parameters;
    }

    /**
     * @param bool $ignoreHeadRoutes
     * @return LaravelRouteParser
     */
    public function setIgnoreHeadRoutes(bool $ignoreHeadRoutes): LaravelRouteParser
    {
        $this->ignoreHeadRoutes = $ignoreHeadRoutes;
        return $this;
    }
}
