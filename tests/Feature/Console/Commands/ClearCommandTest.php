<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Console\Commands;

use Illuminate\Testing\PendingCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestWithTemporaryFilesTrait;
use Mockery\MockInterface;

class ClearCommandTest extends TestCase
{
    use TestWithTemporaryFilesTrait;

    public function tearDown(): void
    {
        $this->clearTemporaryDirectory();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function removesAllCache(): void
    {
        $config = $this->partialMock(Config::class, fn (MockInterface $mock) => $mock->allows([
            'getProviderNames' => ['foo', 'bar', 'baz'],
            'getCacheDirectory' => $this->getTemporaryDirectory(),
        ]));
        assert($config instanceof Config);

        foreach (array_slice($config->getProviderNames(), 1) as $providerName) {
            touch($config->getCacheFileName($providerName));
        }

        $command = $this->artisan('openapi-validator:clear --all');
        assert($command instanceof PendingCommand);

        $command
            ->assertOk()
            ->run();

        foreach ($config->getProviderNames() as $providerName) {
            $this->assertFileDoesNotExist($config->getCacheFileName($providerName));
        }
    }
}
