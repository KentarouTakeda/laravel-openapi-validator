<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Composer\InstalledVersions;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Console\Commands\CacheCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Console\Commands\ClearCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Console\Commands\PublishCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Events\RequestValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Events\ResponseValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Listeners\LogRequestValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Listeners\LogResponseValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Renderer\Rfc7807Renderer;

class ServiceProvider extends BaseServiceProvider
{
    public const CONFIG_PATH = __DIR__.'/../config/openapi-validator.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'openapi-validator');

        $this->app->bind(ErrorRendererInterface::class, Rfc7807Renderer::class);
    }

    public function boot(
        Config $config,
        Dispatcher $eventDispatcher,
    ): void {
        $this->publishes([
            self::CONFIG_PATH => config_path('openapi-validator.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheCommand::class,
                ClearCommand::class,
                PublishCommand::class,
            ]);
        }

        if (InstalledVersions::isInstalled('swagger-api/swagger-ui')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/swagger-ui.php');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'openapi-validator');
        }

        $config->getReqErrorLogLevel() && $eventDispatcher->listen(
            RequestValidationFailed::class,
            LogRequestValidationFailed::class
        );

        $config->getResErrorLogLevel() && $eventDispatcher->listen(
            ResponseValidationFailed::class,
            LogResponseValidationFailed::class
        );
    }
}
