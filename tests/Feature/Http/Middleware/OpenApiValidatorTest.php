<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\OpenApiValidator;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OpenApiValidatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->bind(
            SchemaRepository::class,
            fn () => Mockery::mock(SchemaRepository::class)->allows([
                'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
                'getResponseValidator' => $this->mockValidator()->getResponseValidator(),
            ])
        );

        $this->mock(Config::class, fn (MockInterface $mock) => $mock->allows([
            'getDefaultProviderName' => 'laravel-openapi',
            'getErrorOnNoPath' => true,
            'getIncludeReqErrorInResponse' => true,
            'getIncludeResErrorInResponse' => true,
            'getIncludeTraceInResponse' => true,
            'getNonValidatedResponseCodes' => [],
        ]));
    }

    private function mockValidator(): ValidatorBuilder
    {
        $json = json_encode([
            'paths' => [
                '/' => [
                    'post' => [
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

    /**
     * @test
     */
    public function requestAndResponse(): void
    {
        Route::post('/', fn () => ['data' => [42]])->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk()
        ;
    }

    /**
     * @test
     */
    public function throwsPathNotFoundException(): void
    {
        Route::get('/not-found', fn () => 'Hello')->middleware(OpenApiValidator::class);

        $this->get('/not-found')
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('status', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('title', class_basename(NoPath::class))
        ;
    }

    /**
     * @test
     */
    public function returnsBadRequest(): void
    {
        Route::post('/', fn () => 'Hello')->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1, 'foo']])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('status', Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('title', class_basename(InvalidBody::class))
            ->assertJsonPath('detail', "Value expected to be 'integer', but 'string' given.")
            ->assertJsonPath('pointer', ['hoge', 1])
        ;
    }

    /**
     * @test
     */
    public function returnsInvalidBody(): void
    {
        Route::post('/', fn () => ['data' => [true]])->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]]
        )
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('status', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('title', class_basename(InvalidBody::class))
            ->assertJsonPath('detail', "Value expected to be 'integer', but 'boolean' given.")
            ->assertJsonPath('pointer', ['data', 0])
        ;
    }

    /**
     * @test
     */
    public function returnsHttpException(): void
    {
        Route::post('/', fn () => abort(404, 'foo'))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('detail', 'foo')
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class))
        ;
    }

    /**
     * @test
     */
    public function returnsModelNotFoundException(): void
    {
        Route::post('/', fn () => throw new ModelNotFoundException())->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class))
            ->assertSeeText(class_basename(ModelNotFoundException::class))
            ->assertSeeText(class_basename(NotFoundHttpException::class))
        ;
    }
}
