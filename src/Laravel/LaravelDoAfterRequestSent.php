<?php namespace LaravelSwagger\Laravel;

use LaravelSwagger\Controllers\DoAfterRequestSent;

/**
 * Class LaravelDoAfterRequestSent
 * @package LaravelSwagger\Laravel
 */
class LaravelDoAfterRequestSent implements DoAfterRequestSent
{

    public function doAfterRequestSent(callable $do)
    {
        app()->terminating($do);
    }
}
