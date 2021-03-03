<?php namespace LaravelSwagger\OpenApi;

/**
 * Class DefinedRoute
 * @package LaravelSwagger\OpenApi
 */
class DefinedRoute
{

    public string $controller;

    public string $path;

    protected Method $method;

    /**
     * @var DefinedParameter[]
     */
    public array $parameters = [];

    public  string $name = '';

    protected function __construct()
    {
        $this->method = Method::get();
    }

    public static function fromControllerAndPath(string $controller, string $path) : self
    {
        $definedRoute = new self;
        $definedRoute->controller = $controller;
        $definedRoute->path = $path;
        return $definedRoute;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getOpenApiMethodName()
    {
        return $this->method->getOpenApiName();
    }

    public function setMethodFromLaravelName(string $methodName) : self
    {
        $this->method = Method::fromLaravelMethodName($methodName);
        return $this;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function hasName()
    {
        return !empty($this->name);
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
}
