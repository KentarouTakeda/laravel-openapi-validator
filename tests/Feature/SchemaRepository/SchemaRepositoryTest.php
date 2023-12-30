<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\SchemaRepository;

use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\L5SwaggerResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SchemaRepositoryTest extends TestCase
{
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
}
