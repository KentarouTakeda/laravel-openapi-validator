<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\OpenApiValidator;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
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

        config()->set([
            'logging.default' => 'null',
            'get_default_provider_name' => 'laravel-openapi',
            'openapi-validator.error_on_no_path' => true,
            'openapi-validator.include_req_error_in_response' => true,
            'openapi-validator.include_res_error_in_response' => true,
            'openapi-validator.include_trace_in_response' => true,
            'openapi-validator.non_validated_response_codes' => [],
            'openapi-validator.request_error_log_level' => 'debug',
            'openapi-validator.response_error_log_level' => 'debug',
            'openapi-validator.respond_with_error_on_response_validation_failure' => true,
        ]);
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
        $this->withoutExceptionHandling();

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

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
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
    public function notReturnsResponseErrorIfTheParameterIsSet(): void
    {
        Route::post('/', fn () => ['data' => [true]])
            ->middleware(OpenApiValidator::config(skipResponseValidation: true));

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk();
    }

    /**
     * @test
     */
    public function notReturnsResponseErrorIfTheOptionIsSet(): void
    {
        config()->set([
            'openapi-validator.respond_with_error_on_response_validation_failure' => false,
        ]);

        Route::post('/', fn () => ['data' => [true]])->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk();
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
