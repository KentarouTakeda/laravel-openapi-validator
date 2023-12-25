<?php

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature;

use KentarouTakeda\Laravel\OpenApiValidator\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            OpenApiServiceProvider::class,
            ServiceProvider::class,
        ];
    }
}
