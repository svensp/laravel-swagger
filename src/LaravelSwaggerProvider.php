<?php

namespace LaravelSwagger;

use Illuminate\Support\Facades\Config;
use LaravelSwagger\Commands\GenerateOpenApiCommand;
use LaravelSwagger\Filesystem\FileSystemApiDocIO;
use LaravelSwagger\OpenApi\Updater;

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
        $this->registerPublishedFiles();
        $this->passConfigSettingsToUpdater();
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

    private function registerPublishedFiles(): void
    {
        $this->publishes([
            $this->packageConfigFile => config_path('open-api.php'),
        ]);
    }

    private function passConfigSettingsToUpdater()
    {
        $this->app->resolving(FileSystemApiDocIO::class, function (FileSystemApiDocIO $apiDoc) {
            $aliases = Config::get('open-api.aliases', []);
            foreach ($aliases as $alias => $path) {
                $apiDoc->setAlias($alias, $path);
            }
        });
    }
}
