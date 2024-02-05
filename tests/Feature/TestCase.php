<?php

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature;

use KentarouTakeda\Laravel\OpenApiValidator\ServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;

use function KentarouTakeda\Laravel\OpenApiValidator\isl5SwaggerInstalled;
use function KentarouTakeda\Laravel\OpenApiValidator\isLaravelOpenAPIInstalled;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        $providers = [
            ServiceProvider::class,
        ];

        if (isl5SwaggerInstalled()) {
            $providers[] = L5SwaggerServiceProvider::class;
        }

        if (isLaravelOpenAPIInstalled()) {
            $providers[] = OpenApiServiceProvider::class;
        }

        return $providers;
    }
}
