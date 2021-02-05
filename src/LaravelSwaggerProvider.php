<?php

namespace LaravelSwagger;

use LaravelSwagger\Commands\GenerateOpenApiCommand;

/**
 * Class Provider
 */
class LaravelSwaggerProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateOpenApiCommand::class,
            ]);
        }
    }

}