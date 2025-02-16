<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\SchemaRepository;

use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\L5SwaggerResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestWithTemporaryFilesTrait;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class SchemaRepositoryTest extends TestCase
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
    public function laravelOpenApiResolverIsUsed(): void
    {
        $laravelOpenApiResolver = $this->mock(LaravelOpenApiResolver::class, function ($mock) {
            $mock->shouldReceive('getJson')->andReturn('{"foo":"bar"}');
        });

        app()->makeWith(SchemaRepository::class, [
            'providerName' => 'laravel-openapi',
        ]);

        $laravelOpenApiResolver->shouldHaveReceived('getJson')->once();
    }

    /**
     * @test
     */
    public function L5SwaggerResolverIsUsed(): void
    {
        $l5SwaggerResolver = $this->mock(L5SwaggerResolver::class, function ($mock) {
            $mock->shouldReceive('getJson')->andReturn('{"foo":"bar"}');
        });

        app()->makeWith(SchemaRepository::class, [
            'providerName' => 'l5-swagger',
        ]);

        $l5SwaggerResolver->shouldHaveReceived('getJson')->once();
    }

    /**
     * @test
     */
    public function anyResolverIsUotUsedWhenCacheIsExists(): void
    {
        $this->partialMock(Config::class, fn (MockInterface $mock) => $mock->allows([
            'getCacheDirectory' => $this->getTemporaryDirectory(),
            'getProviderSettings' => [],
        ]));

        $config = app()->make(Config::class);

        $filesystem = app()->make(Filesystem::class);

        $filesystem->put(
            $config->getCacheFileName('foo'),
            '{}',
        );

        $schemaRepository = app()->makeWith(SchemaRepository::class, [
            'providerName' => 'foo',
        ]);
        assert($schemaRepository instanceof SchemaRepository);

        $this->assertSame('{}', $schemaRepository->getJson());
    }
}
