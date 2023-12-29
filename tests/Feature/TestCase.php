<?php

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature;

use KentarouTakeda\Laravel\OpenApiValidator\ServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            L5SwaggerServiceProvider::class,
            OpenApiServiceProvider::class,
            ServiceProvider::class,
        ];
    }
}
