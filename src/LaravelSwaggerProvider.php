<?php

namespace LaravelSwagger;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelSwagger\Commands\GenerateOpenApiCommand;
use LaravelSwagger\Controllers\ApiDocController;
use LaravelSwagger\Controllers\Cache;
use LaravelSwagger\Controllers\DoAfterRequestSent;
use LaravelSwagger\Filesystem\FileSystemApiDocIO;
use LaravelSwagger\Laravel\LaravelCache;
use LaravelSwagger\Laravel\LaravelDoAfterRequestSent;
use LaravelSwagger\OpenApi\ApiDocIO;
use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\PHPDoc\PHPDocControllerParser;

/**
 * Class Provider
 */
class LaravelSwaggerProvider extends ServiceProvider
{

    protected string $packageConfigFile = __DIR__ . '/../config/open-api.php';

    public function register()
    {
        $this->setDefaultConfig();
    }

    public function boot()
    {
        $this->registerImplementations();
        $this->registerCommands();
        $this->registerPublishedFiles();
        $this->passConfigSettingsToUpdater();

        Route::macro('apiDoc', function ($route, $filePath) {
            return Route::get($route, function () use ($filePath) {
                /**
                 * @var ApiDocController $controller
                 */
                $controller = app(ApiDocController::class);
                $controller->setFilepath($filePath);
                return $controller->sendApiDoc();
            });
        });
    }

    private function setDefaultConfig()
    {
        $this->mergeConfigFrom($this->packageConfigFile, 'open-api');
    }

    private function registerImplementations()
    {
        $this->app->bind(ApiDocIO::class, FileSystemApiDocIO::class);
        $this->app->bind(ControllerParser::class, PHPDocControllerParser::class);
        $this->app->bind(Cache::class, LaravelCache::class);
        $this->app->bind(DoAfterRequestSent::class, LaravelDoAfterRequestSent::class);
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
