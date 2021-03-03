<?php namespace LaravelSwagger\OpenApi;

/**
 * Class FoundRoutesByApiDoc
 * @package LaravelSwagger\OpenApi
 */
class FoundRoutesByApiDoc
{
    private array $routesByApiDoc = [];

    private array $usedRoutesByApiDoc = [];

    public function addRouteToApiDocPath(string $apiDocPath, FoundRoute $foundRoute)
    {
        $newApiDocPath = !array_key_exists($apiDocPath, $this->routesByApiDoc);
        if ($newApiDocPath) {
            $this->routesByApiDoc[$apiDocPath] = [];
        }

        $identifier = $foundRoute->getOpenApiMethodName().'-'.$foundRoute->getPath();
        $this->routesByApiDoc[$apiDocPath][$identifier] = $foundRoute;
    }

    public function markRouteUsedOnApiDocPath(string $apiDocPath, DefinedRoute $definedRoute)
    {
        $newApiDocPath = !array_key_exists($apiDocPath, $this->usedRoutesByApiDoc);
        if ($newApiDocPath) {
            $this->usedRoutesByApiDoc[$apiDocPath] = [];
        }

        $identifier = $definedRoute->getOpenApiMethodName().'-'.$definedRoute->getPath();
        $this->usedRoutesByApiDoc[$apiDocPath][$identifier] = $definedRoute;
    }

    public function eachUnused(callable $do)
    {
        $unusedRoutes = $this->routesByApiDoc;

        foreach ($this->usedRoutesByApiDoc as $apiDocPath => $routes) {
            if (!array_key_exists($apiDocPath, $unusedRoutes)) {
                continue;
            }

            foreach ($routes as $identifier => $route) {
                unset($unusedRoutes[$apiDocPath][$identifier]);
            }
        }

        foreach ($unusedRoutes as $apiDocPath => $routes) {
            foreach ($routes as $route) {
                $do($apiDocPath, $route);
            }
        }
    }
}
