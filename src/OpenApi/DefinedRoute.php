<?php namespace LaravelSwagger\OpenApi;

/**
 * Class DefinedRoute
 * @package LaravelSwagger\OpenApi
 */
class DefinedRoute
{

    public string $controller;

    public string $path;

    protected string $method = self::METHOD_GET;

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_PATCH = 'patch';
    const METHOD_DELETE = 'delete';
    const METHOD_OPTIONS = 'options';

    public function setMethodGet()
    {
        $this->method = self::METHOD_GET;
    }

    public function setMethodPost()
    {
        $this->method = self::METHOD_POST;
    }

    public function setMethodPut()
    {
        $this->method = self::METHOD_PUT;
    }

    public function setMethodPatch()
    {
        $this->method = self::METHOD_PATCH;
    }

    public function setMethodDelete()
    {
        $this->method = self::METHOD_DELETE;
    }

    public function setMethodOptions()
    {
        $this->method = self::METHOD_OPTIONS;
    }

    public function getMethodName() : string
    {
        return $this->method;
    }


}