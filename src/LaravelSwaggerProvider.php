<?php

namespace LaravelSwagger;

use LaravelSwagger\Commands\GenerateOpenApiCommand;

/**
 * Class Provider
 */
class LaravelSwaggerProvider extends \Illuminate\Support\ServiceProvider
{

    protected string $packageConfigFile = __DIR__ . '/../config/open-api.php';

    public function register()
    {
        $this->setDefaultConfig();
    }

    public function boot()
    {
        $this->registerCommands();
        $this->registerPublishedFFiles();
    }

    private function setDefaultConfig()
    {
        $this->mergeConfigFrom($this->packageConfigFile, 'open-api');
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
            $this->packageConfigFile => config_path('open-api.php'),
        ]);
    }
}
