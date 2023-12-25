<?php

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature;

use KentarouTakeda\Laravel\OpenApiValidator\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
