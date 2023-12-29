<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\SchemaRepository;

use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SchemaRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function exceptionThrownForNonExistentProvider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'laravel-openapi-validator: Provider not-found is not defined'
        );

        app()->makeWith(SchemaRepository::class, [
            'providerName' => 'not-found',
        ]);
    }

    /**
     * @test
     */
    public function laravelOpenApiResolverIsUsed(): void
    {
        $laravelOpenApiResolver = $this->mock(LaravelOpenApiResolver::class, function ($mock) {
            $mock->shouldReceive('supports')->andReturn('laravel-openapi');
            $mock->shouldReceive('getJson')->andReturn('{"foo":"bar"}');
        });

        app()->makeWith(SchemaRepository::class, [
            'providerName' => 'laravel-openapi',
        ]);

        $laravelOpenApiResolver->shouldHaveReceived('getJson')->once();
    }
}
