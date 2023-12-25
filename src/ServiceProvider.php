<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public const CONFIG_PATH = __DIR__.'/../config/openapi-validator.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'openapi-validator');
    }

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('openapi-validator.php'),
        ], 'config');
    }
}
