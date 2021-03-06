<?php namespace LaravelSwagger\Controllers;

/**
 * Interface DoAfterRequestSent
 * @package LaravelSwagger\Controllers
 */
interface DoAfterRequestSent
{

    public function doAfterRequestSent(callable $do);
}
