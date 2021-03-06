<?php namespace LaravelSwagger\Laravel;

use LaravelSwagger\Controllers\ResponseBuilder;

/**
 * Class LaravelResponseBuilder
 * @package LaravelSwagger\Laravel
 */
class LaravelResponseBuilder implements ResponseBuilder
{

    public function jsonResponse($content)
    {
        return response()->json($content);
    }
}
