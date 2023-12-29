<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Console\Commands;

use Illuminate\Contracts\Config\Repository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestWithTemporaryFilesTrait;

class ClearCommandTest extends TestCase
{
    use TestWithTemporaryFilesTrait;

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('openapi-validator.cache_directory', $this->getTemporaryDirectory());
        });
    }

    public function tearDown(): void
    {
        $this->clearTemporaryDirectory();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function test(): void
    {
    }
}
