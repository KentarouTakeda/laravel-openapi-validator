<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Console\Commands;

use Illuminate\Testing\PendingCommand;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestWithTemporaryFilesTrait;
use Mockery\MockInterface;

class CacheCommandTest extends TestCase
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
    public function createsAllCache(): void
    {
        $config = $this->partialMock(Config::class, fn (MockInterface $mock) => $mock->allows([
            'getProviderNames' => ['foo', 'bar'],
            'getCacheDirectory' => $this->getTemporaryDirectory(),
        ]));
        assert($config instanceof Config);

        app()->bind(
            SchemaRepository::class,
            fn () => \Mockery::mock(SchemaRepository::class)->allows([
                'getJson' => '{"foo": "bar"}',
            ])
        );

        $command = $this->artisan('openapi-validator:cache --all');
        assert($command instanceof PendingCommand);

        $command
            ->assertOk()
            ->run();

        foreach ($config->getProviderNames() as $providerName) {
            $this->assertFileExists($config->getCacheFileName($providerName));
        }
    }
}
