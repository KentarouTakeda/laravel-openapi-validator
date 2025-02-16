<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\SchemaRepository;

use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use Vyuldashev\LaravelOpenApi\Attributes\Operation;
use Vyuldashev\LaravelOpenApi\Attributes\PathItem;

use function KentarouTakeda\Laravel\OpenApiValidator\isLaravelOpenAPIInstalled;

class LaravelOpenApiResolverTest extends TestCase
{
    private LaravelOpenApiResolver $laravelOpenApiResolver;

    public static function setUpBeforeClass(): void
    {
        if (!isLaravelOpenAPIInstalled()) {
            self::markTestSkipped('Laravel OpenAPI is not installed.');
        }

        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        $laravelOpenApiResolver = app()->make(LaravelOpenApiResolver::class);

        $this->laravelOpenApiResolver = $laravelOpenApiResolver;
        Route::get('/', LaravelOpenApiResolverTestController::class);
    }

    public function test(): void
    {
        $json = $this->laravelOpenApiResolver->getJson([
            'collection' => 'default',
        ]);

        $spec = json_decode($json, true);
        $this->assertNotNull($spec);

        $this->assertSame('3.0.2', $spec['openapi']);
        $this->assertSame([
            'summary' => 'foo',
            'description' => 'bar',
        ], $spec['paths']['/']['get']);
    }
}

#[PathItem]
class LaravelOpenApiResolverTestController
{
    /**
     * foo
     * 
     * bar
     */
    #[Operation]
    public function __invoke(): void
    {
    }
}
