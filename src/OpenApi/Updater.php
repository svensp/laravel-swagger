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
     * Updater constructor.
     * @param ControllerParser $controllerParser
     * @param ApiDocIO $reader
     */
    public function __construct(ControllerParser $controllerParser, ApiDocIO $reader) {
        $this->apiDocIO = $reader;
        $this->controllerParser = $controllerParser;
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
        foreach($definedRoutes as $definedRoute) {
            $controller = $this->controllerParser->parse( $definedRoute->controller );

            $this->addController($controller, $definedRoute);
        }
    }

    private function updateControllers()
    {
        foreach($this->routesByController as $routesByController) {
            $this->updateController($routesByController);
        }
    }

    private function addController(Controller $controller, DefinedRoute $definedRoute)
    {
        $controllerWithRoutes = $this->createOrGet($controller);
        $controllerWithRoutes->routes[] = $definedRoute;
    }

    private function updateController(ControllerWithRoutes $controllerWithRoutes)
    {
        $this->apiDocIO->update($controllerWithRoutes->controller->apiDocPath, function($openApiSpecification) {

            $this->setIfNotPresent($openApiSpecification, 'openapi', '3.0.3');
            $this->setIfNotPresent($openApiSpecification, 'info.title', 'CHANGEME');
            $this->setIfNotPresent($openApiSpecification, 'info.version', '0.1.0');
            $this->setIfNotPresent($openApiSpecification, 'paths', []);

            return $openApiSpecification;
        });
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
        if($notPresent) {
            $this->routesByController[$key] = ControllerWithRoutes::fromController($controller);
        }

        return $this->routesByController[$key];
    }

}