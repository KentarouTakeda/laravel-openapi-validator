<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\SchemaRepository;

use Illuminate\Contracts\Config\Repository;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\L5SwaggerResolver;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

class L5SwaggerResolverTest extends TestCase
{
    private L5SwaggerResolver $l5SwaggerResolver;

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('l5-swagger.defaults.paths.docs', storage_path('framework/testing'));
            $config->set('l5-swagger.documentations.default.paths.annotations', [getcwd().'/tests/Feature']);
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $l5SwaggerResolver = app()->make(L5SwaggerResolver::class);
        assert($l5SwaggerResolver instanceof L5SwaggerResolver);

        $this->l5SwaggerResolver = $l5SwaggerResolver;
    }

    public function test(): void
    {
        $json = $this->l5SwaggerResolver->getJson([
            'documentation' => 'default',
        ]);

        $spec = json_decode($json, true);
        $this->assertNotNull($spec);

        $this->assertSame('3.0.0', $spec['openapi']);
        $this->assertSame(['title' => 'foo', 'version' => '1.0.0'], $spec['info']);
        $this->assertIsArray($spec['paths']['/']['get']['responses']['200']);
        $this->assertIsArray($spec['components']['schemas']['L5SwaggerResolverTestUser']);
    }
}

#[Info(
    version: '1.0.0',
    title: 'foo',
)]
class OpenAPI
{
}

#[Schema]
class L5SwaggerResolverTestUser
{
    #[Property]
    public int $id;
}

class L5SwaggerResolverTestController
{
    #[Get(
        path: '/',
        responses: [
            new Response(response: 200, description: 'OK'),
        ]
    )]
    public function __invoke(): void
    {
    }
}