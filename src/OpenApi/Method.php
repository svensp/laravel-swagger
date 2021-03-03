<?php namespace LaravelSwagger\OpenApi;

/**
 * Class Method
 * @package LaravelSwagger\OpenApi
 */
class Method
{

    protected string $internalMethodName;

    const INTERNAL_METHOD_GET = 'get';
    const INTERNAL_METHOD_POST = 'post';
    const INTERNAL_METHOD_PUT = 'put';
    const INTERNAL_METHOD_PATCH = 'patch';
    const INTERNAL_METHOD_DELETE = 'delete';
    const INTERNAL_METHOD_OPTIONS = 'options';

    public static function fromLaravelMethodName(string $laravelMethodName) : self
    {
        $instance = new self();
        $instance->internalMethodName = $laravelMethodName;
        return $instance;
    }

    public static function fromOpenApiMethodName(string $openApiMethodName) : self
    {
        return self::fromLaravelMethodName($openApiMethodName);
    }

    protected static function make($internalMethodName) : self
    {
        $instance = new self();
        $instance->internalMethodName = $internalMethodName;
        return $instance;
    }

    public static function get() : self
    {
        return self::make(self::INTERNAL_METHOD_GET);
    }

    public static function post() : self
    {
        return self::make(self::INTERNAL_METHOD_POST);
    }

    public static function put() : self
    {
        return self::make(self::INTERNAL_METHOD_PUT);
    }

    public static function patch() : self
    {
        return self::make(self::INTERNAL_METHOD_PATCH);
    }

    public static function delete() : self
    {
        return self::make(self::INTERNAL_METHOD_DELETE);
    }

    public static function options() : self
    {
        return self::make(self::INTERNAL_METHOD_OPTIONS);
    }

    public function getOpenApiName() : string
    {
        return $this->internalMethodName;
    }

    public function isPost() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_POST;
    }

    public function isGet() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_GET;
    }

    public function isPut() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_PUT;
    }

    public function isPatch() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_PATCH;
    }

    public function isDelete() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_DELETE;
    }

    public function isOptions() : bool
    {
        return $this->internalMethodName === self::INTERNAL_METHOD_OPTIONS;
    }

}