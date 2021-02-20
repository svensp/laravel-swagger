<?php namespace LaravelSwagger\OpenApi;

/**
 * Interface ControllerParser
 * @package LaravelSwagger\OpenApi
 */
interface ControllerParser
{

    /**
     * @param string $classPath
     * @return Controller
     * @throws NoApiDocSpecifiedException
     */
    public function parse(string $classPath) : Controller;
}
