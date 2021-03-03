<?php namespace LaravelSwagger\OpenApi;

use Illuminate\Support\Arr;

/**
 * Class Updater
 * @package LaravelSwagger\OpenApi
 */
class Updater
{
    /**
     * @var ApiDocIO
     */
    private ApiDocIO $apiDocIO;
    /**
     * @var ControllerParser
     */
    private ControllerParser $controllerParser;

    /**
     * @var ControllerWithRoutes[]
     */
    private array $routesByController = [];

    /**
     * @var callable
     */
    private $controllerWithoutApidocHook;

    /**
     * @var callable
     */
    private $unknownRouteHook;

    private FoundRoutesByApiDoc $foundRoutesByApiDoc;

    public function __construct(
        ControllerParser $controllerParser,
        ApiDocIO $reader,
        FoundRoutesByApiDoc $foundRoutesByApiDoc
    ) {
        $this->apiDocIO = $reader;
        $this->controllerParser = $controllerParser;
        $this->controllerWithoutApidocHook = function () {
        };
        $this->unknownRouteHook = function () {
        };
        $this->foundRoutesByApiDoc = $foundRoutesByApiDoc;
    }

    public function onControllerWithoutApidoc(callable $do) : self
    {
        $this->controllerWithoutApidocHook = $do;
        return $this;
    }

    public function onUnknownRoute(callable $do) : self
    {
        $this->unknownRouteHook = $do;
        return $this;
    }

    /**
     * @param DefinedRoute[] $definedRoutes
     */
    public function update(array $definedRoutes)
    {
        $this->parseRoutes($definedRoutes);
        $this->updateControllers();
    }

    /**
     * @param DefinedRoute[] $definedRoutes
     */
    private function parseRoutes(array $definedRoutes)
    {
        foreach ($definedRoutes as $definedRoute) {
            try {
                $controller = $this->controllerParser->parse($definedRoute->controller);
            } catch (NoApiDocSpecifiedException $e) {
                $controlledWithoutApidocHook = $this->controllerWithoutApidocHook;
                $controlledWithoutApidocHook($definedRoute);
                continue;
            }

            $this->addController($controller, $definedRoute);
        }
    }

    private function updateControllers()
    {
        foreach ($this->routesByController as $routesByController) {
            $this->updateController($routesByController);
        }
        $this->callUnknownRouteHooks();
    }

    private function callUnknownRouteHooks()
    {
        $this->foundRoutesByApiDoc->eachUnused(function (string $apiDocPath, FoundRoute $foundRoute) {
            call_user_func($this->unknownRouteHook, $apiDocPath, $foundRoute);
        });
    }

    private function updateController(ControllerWithRoutes $controllerWithRoutes)
    {
        $this->apiDocIO->update(
            $controllerWithRoutes->controller->apiDocPath,
            function ($openApiSpecification) use ($controllerWithRoutes) {
                $this->rememberExistingRoutes($controllerWithRoutes->controller->apiDocPath, $openApiSpecification);
                $openApiSpecification = $this->setSpecificationDefaults($openApiSpecification);
                $openApiSpecification = $this->setRoutesInApiSpecification(
                    $controllerWithRoutes,
                    $openApiSpecification
                );
                $this->markDefinedRoutesUsed($controllerWithRoutes);


                return $openApiSpecification;
            }
        );
    }

    private function addController(Controller $controller, DefinedRoute $definedRoute)
    {
        $controllerWithRoutes = $this->createOrGet($controller);
        $controllerWithRoutes->routes[] = $definedRoute;
    }

    private function rememberExistingRoutes($openApiDocPath, $openApiSpecification)
    {
        foreach (Arr::get($openApiSpecification, 'paths', []) as $path => $definitions) {
            $methods = array_keys($definitions);
            foreach ($methods as $method) {
                $foundRoute = FoundRoute::fromPathAndOpenApiMethodName($path, $method);
                $this->foundRoutesByApiDoc->addRouteToApiDocPath($openApiDocPath, $foundRoute);
            }
        }
    }

    private function markDefinedRoutesUsed(ControllerWithRoutes $controllerWithRoutes)
    {
        foreach ($controllerWithRoutes->routes as $route) {
            $this->foundRoutesByApiDoc->markRouteUsedOnApiDocPath(
                $controllerWithRoutes->controller->apiDocPath,
                $route
            );
        }
    }

    private function setSpecificationDefaults($openApiSpecification)
    {
        $this->setIfNotPresent($openApiSpecification, 'openapi', '3.0.3');
        $this->setIfNotPresent($openApiSpecification, 'info.title', 'CHANGEME');
        $this->setIfNotPresent($openApiSpecification, 'info.version', '0.1.0');
        $this->setIfNotPresent($openApiSpecification, 'paths', []);
        return $openApiSpecification;
    }

    private function setRoutesInApiSpecification(ControllerWithRoutes $controllerWithRoutes, $openApiSpecification)
    {
        foreach ($controllerWithRoutes->routes as $route) {
            $basePath = $this->basePathFromRoute($route);

            $hasName = !empty($route->name);
            if ($hasName) {
                $this->setIfNotPresent($openApiSpecification, "$basePath.operationId", $route->name);
            }
            $this->setIfNotPresent($openApiSpecification, "$basePath.summary", 'TODO: Summary');

            $openApiSpecification = $this->setParametersForRoute($route, $openApiSpecification);
        }

        return $openApiSpecification;
    }

    private function setParametersForRoute(DefinedRoute $route, $openApiSpecification)
    {
        $basePath = $this->basePathFromRoute($route);

        foreach ($route->parameters as $index => $parameter) {
            $this->setIfNotPresent($openApiSpecification, "$basePath.parameters.$index.name", $parameter->name);
            $this->setIfNotPresent($openApiSpecification, "$basePath.parameters.$index.in", 'path');
            $this->setIfNotPresent($openApiSpecification, "$basePath.parameters.$index.required", true);
            $this->setIfNotPresent(
                $openApiSpecification,
                "$basePath.parameters.$index.description",
                $parameter->description
            );
        }

        return $openApiSpecification;
    }

    private function basePathFromRoute(DefinedRoute $route)
    {
        $path = $route->path;
        $method = $route->getOpenApiMethodName();

        return "paths.{$path}.{$method}";
    }

    private function setIfNotPresent(&$array, $key, $defaultValue)
    {
        $value = Arr::get($array, $key, $defaultValue);
        Arr::set($array, $key, $value);
    }

    private function createOrGet(Controller $controller): ControllerWithRoutes
    {
        $key = $controller->getKey();

        $notPresent = !array_key_exists($key, $this->routesByController);
        if ($notPresent) {
            $this->routesByController[$key] = ControllerWithRoutes::fromController($controller);
        }

        return $this->routesByController[$key];
    }
}
