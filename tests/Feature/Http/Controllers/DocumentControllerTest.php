<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Http\Controllers;

use Illuminate\Config\Repository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DocumentControllerTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], fn (Repository $config) => $config->set([
            'openapi-validator.is_swagger_ui_enabled' => true,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        ]));
    }

    #[Test]
    public function indexShouldRedirectToDefaultProvider(): void
    {
        $this->get(route('openapi-validator.document.index'))
            ->assertRedirect(route('openapi-validator.document.laravel-openapi'));
    }

    #[Test]
    public function viewShouldReturnDocument(): void
    {
        $this->get(route('openapi-validator.document.laravel-openapi'))
            ->assertOk()
            ->assertViewIs('openapi-validator::documents')
            ->assertViewHas('json')
            ->assertSee('<title>Swagger UI</title>', false);
    }

    #[Test]
    public function assetShouldReturnsNotFoundWhenFileNotFound(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'not-found.css']))
            ->assertNotFound();
    }

    #[Test]
    public function assetShouldReturnsAssetWithJsMimeType(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'swagger-ui-bundle.js']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
    }

    #[Test]
    public function assetShouldReturnsAssetWithCssMimeType(): void
    {
        $this->get(route('openapi-validator.asset', ['path' => 'swagger-ui.css']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }
}
