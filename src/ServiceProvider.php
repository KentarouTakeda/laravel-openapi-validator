<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KentarouTakeda\Laravel\OpenApiValidator\Console\CacheCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Console\ClearCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\ValidateRequestResponse;
use KentarouTakeda\Laravel\OpenApiValidator\Renderer\ErrorRenderer;

class ServiceProvider extends BaseServiceProvider
{
    public const CONFIG_PATH = __DIR__.'/../config/openapi-validator.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'openapi-validator');

        $this->app->bind(ErrorRendererInterface::class, ErrorRenderer::class);

        $this->app->when(ValidateRequestResponse::class)
            ->needs('$errorOnNoPath')
            ->giveConfig('openapi-validator.error_on_no_path');

        $this->app->when(ValidateRequestResponse::class)
            ->needs('$includeReqErrorInResponse')
            ->giveConfig('openapi-validator.include_req_error_in_response');

        $this->app->when(ValidateRequestResponse::class)
            ->needs('$includeResErrorInResponse')
            ->giveConfig('openapi-validator.include_res_error_in_response');

        $this->app->when(ValidateRequestResponse::class)
            ->needs('$includeTraceInResponse')
            ->giveConfig('openapi-validator.include_trace_in_response');
    }

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('openapi-validator.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheCommand::class,
                ClearCommand::class,
            ]);
        }
    }
}
