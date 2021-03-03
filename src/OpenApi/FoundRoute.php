<?php namespace LaravelSwagger\OpenApi;

/**
 * Class FoundRoute
 * @package LaravelSwagger\OpenApi
 */
class FoundRoute
{

    protected string $path;

    protected Method $method;

    public static function fromPathAndOpenApiMethodName(string $path, string $openApiMethodName) : self
    {
        $instance = new self;
        $instance->path = $path;
        $instance->method = Method::fromOpenApiMethodName($openApiMethodName);
        return $instance;
    }

    public function getOpenApiMethodName() : string
    {
        return $this->method->getOpenApiName();
    }

    public function isPath($path) : bool
    {
        return $this->path === $path;
    }

    public function isGet() : bool
    {
        return $this->method->isGet();
    }

    public function isPost() : bool
    {
        return $this->method->isPost();
    }

    public function isPut() : bool
    {
        return $this->method->isPut();
    }

    public function isPatch() : bool
    {
        return $this->method->isPatch();
    }

    public function isDelete() : bool
    {
        return $this->method->isDelete();
    }

    public function isOptions() : bool
    {
        return $this->method->isOptions();
    }

    public function getPath() : string
    {
        return $this->path;
    }
}
