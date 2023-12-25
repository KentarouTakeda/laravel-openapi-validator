<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\PathNotFoundException;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\ValidateRequestResponse;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidateRequestResponseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            SchemaRepository::class,
            fn () => Mockery::mock(SchemaRepository::class)->allows([
                'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
                'getResponseValidator' => $this->mockValidator()->getResponseValidator(),
            ])
        );
    }

    private function mockValidator(): ValidatorBuilder
    {
        $json = json_encode([
            'paths' => [
                '/' => [
                    'post' => [
                        'parameters' => [
                            [
                                'name' => 'foo',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'requestBody' => [
                            'content' => [
                                '*/*' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'hoge' => [
                                                'type' => 'array',
                                                'items' => ['type' => 'integer'],
                                            ],
                                        ],
                                        'required' => ['hoge'],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    '*/*' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => ['type' => 'integer'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        assert($json);

        return (new ValidatorBuilder())->fromJson($json);
    }

    #[Test]
    public function requestAndResponse(): void
    {
        Route::post('/', fn () => ['data' => [42]])->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST,
            '/?foo=1', ['hoge' => [1]]
        )->assertOk();
    }

    #[Test]
    public function throwsPathNotFoundException(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path not found: GET /not-found');

        Route::get('/not-found', fn () => 'Hello')->middleware(ValidateRequestResponse::class);

        $this->get('/not-found')
            ->assertOk();
    }

    #[Test]
    public function returnsBadRequest(): void
    {
        Route::post('/', fn () => 'Hello')->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST, '/?foo=1',
            ['hoge' => [1, 'foo']]
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('status', Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('title', class_basename(InvalidBody::class))
            ->assertJsonPath('breadcrumb', ['hoge', 1])
        ;
    }

    #[Test]
    public function returnsInvalidBody(): void
    {
        Route::post('/', fn () => ['data' => ['foo']])->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST, '/?foo=1',
            ['hoge' => [1]]
        )
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('status', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('title', class_basename(InvalidBody::class))
            ->assertJsonPath('breadcrumb', ['data', 0])
        ;
    }

    #[Test]
    public function returnsHttpException(): void
    {
        Route::post('/', fn () => abort(404, 'foo'))->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST, '/?foo=1',
            ['hoge' => [1]]
        )
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('detail', 'foo')
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class));
    }

    #[Test]
    public function returnsModelNotFoundException(): void
    {
        Route::post('/', fn () => throw new ModelNotFoundException())->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST, '/?foo=1',
            ['hoge' => [1]]
        )
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class));
    }
}
