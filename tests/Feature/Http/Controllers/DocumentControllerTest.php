<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Http\Controllers;

use Illuminate\Config\Repository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;

use function KentarouTakeda\Laravel\OpenApiValidator\isLaravelOpenAPIInstalled;
use function KentarouTakeda\Laravel\OpenApiValidator\isSwaggerUIInstalled;

class DocumentControllerTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], fn (Repository $config) => $config->set(
            ['openapi-validator.is_swagger_ui_enabled' => true],
        ));
    }

    public static function setUpBeforeClass(): void
    {
        if (!isSwaggerUIInstalled()) {
            self::markTestSkipped('Swagger UI is not installed.');
        }

        parent::setUpBeforeClass();
    }

    /**
     * @test
     */
    public function indexShouldRedirectToDefaultProvider(): void
    {
        $this->get(route('openapi-validator.document.index'))
            ->assertRedirect(route('openapi-validator.document.laravel-openapi'));
    }

    /**
     * @test
     */
    public function viewShouldReturnDocument(): void
    {
        if (!isLaravelOpenAPIInstalled()) {
            $this->markTestSkipped('Laravel OpenAPI is not installed.');
        }

        $this->get(route('openapi-validator.document.laravel-openapi'))
            ->assertOk()
            ->assertViewIs('openapi-validator::documents')
            ->assertViewHas('json')
            ->assertSee('<title>Swagger UI</title>', false);
    }

    /**
     * @test
     */
    public function assetShouldReturnsNotFoundWhenFileNotFound(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'not-found.css']))
            ->assertNotFound();
    }

    /**
     * @test
     */
    public function assetShouldReturnsAssetWithJsMimeType(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'swagger-ui-bundle.js']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
    }

    /**
     * @test
     */
    public function assetShouldReturnsAssetWithCssMimeType(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'swagger-ui.css']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }
}
