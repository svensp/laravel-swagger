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

        $this->registerCommands();
        $this->registerPublishedFFiles();
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateOpenApiCommand::class,
            ]);
        }
    }

    private function registerPublishedFFiles(): void
    {
        $this->publishes([
            __DIR__ . '/../config/open-api.php' => config_path('open-api.php'),
        ]);
    }
}
