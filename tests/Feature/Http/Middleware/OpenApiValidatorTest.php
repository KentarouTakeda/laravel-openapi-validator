<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Http\Middleware;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Events\RequestValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Events\ResponseValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\OpenApiValidator;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\NoResponseCode;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class OpenApiValidatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake([
            RequestValidationFailed::class,
            ResponseValidationFailed::class,
        ]);

        app()->bind(
            SchemaRepository::class,
            fn () => \Mockery::mock(SchemaRepository::class)->allows([
                'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
                'getResponseValidator' => $this->mockValidator()->getResponseValidator(),
            ])
        );

        config()->set([
            'get_default_provider_name' => 'laravel-openapi',
            'openapi-validator.validate_error_responses' => true,
            'openapi-validator.error_on_no_path' => true,
            'openapi-validator.include_req_error_detail_in_response' => true,
            'openapi-validator.include_res_error_detail_in_response' => true,
            'openapi-validator.include_trace_in_response' => true,
            'openapi-validator.include_original_res_in_response' => true,
            'openapi-validator.non_validated_response_codes' => [],
            'openapi-validator.req_error_log_level' => 'debug',
            'openapi-validator.res_error_log_level' => 'debug',
            'openapi-validator.respond_error_on_res_validation_failure' => true,
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
                            '401' => [],
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
        Route::post('/', fn () => ['data' => [42]])->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk()
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function throwsPathNotFoundException(): void
    {
        Route::get('/not-found', fn () => 'Hello')->middleware(OpenApiValidator::class);

        $this->get('/not-found')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('status', Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('title', class_basename(NoPath::class))
        ;

        Event::assertDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
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

        Event::assertDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsInvalidBody(): void
    {
        $response = ['data' => [true]];

        Route::post('/', fn () => $response)->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('status', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('title', class_basename(InvalidBody::class))
            ->assertJsonPath('detail', "Value expected to be 'integer', but 'boolean' given.")
            ->assertJsonPath('pointer', ['data', 0])
            ->assertJsonPath('originalResponse', $response)
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function notReturnsResponseErrorIfTheParameterIsSet(): void
    {
        Route::post('/', fn () => ['data' => [true]])
            ->middleware(OpenApiValidator::config(skipResponseValidation: true));

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk();

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function notReturnsResponseErrorIfTheOptionIsSet(): void
    {
        config()->set([
            'openapi-validator.respond_error_on_res_validation_failure' => false,
        ]);

        Route::post('/', fn () => ['data' => [true]])->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertOk();

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsHttpExceptionWithEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => true,
            'openapi-validator.validate_error_responses' => false,
        ]);

        Route::post('/', fn () => abort(404, 'foo'))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('detail', 'foo')
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class))
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsHttpExceptionWithoutEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => false,
            'openapi-validator.validate_error_responses' => false,
        ]);

        Route::post('/', fn () => abort(404, 'foo'))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonMissingPath('status')
            ->assertJsonMissingPath('detail')
            ->assertJsonMissingPath('title')
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsModelNotFoundExceptionWithEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => true,
            'openapi-validator.validate_error_responses' => false,
        ]);

        Route::post('/', fn () => throw new ModelNotFoundException())->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('status', Response::HTTP_NOT_FOUND)
            ->assertJsonPath('title', class_basename(NotFoundHttpException::class))
            ->assertSeeText(class_basename(ModelNotFoundException::class))
            ->assertSeeText(class_basename(NotFoundHttpException::class))
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsModelNotFoundExceptionWithoutEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => false,
            'openapi-validator.validate_error_responses' => false,
        ]);

        Route::post('/', fn () => throw new ModelNotFoundException())->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonMissingPath('status')
            ->assertJsonMissingPath('title')
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsInternalServerErrorIfErrorResponseValidationIsOccurred(): void
    {
        Route::post('/', fn () => abort(403))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('status', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('title', class_basename(NoResponseCode::class))
            ->assertJsonPath('detail', 'OpenAPI spec contains no such operation [/,post,403]')
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsOriginalErrorIfErrorResponseValidationIsPassedWithEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => true,
        ]);

        Route::post('/', fn () => throw new UnauthorizedHttpException(''))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonPath('status', Response::HTTP_UNAUTHORIZED)
            ->assertJsonPath('title', class_basename(UnauthorizedHttpException::class))
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }

    #[Test]
    public function returnsOriginalErrorIfErrorResponseValidationIsPassedWithoutEnableRenderer(): void
    {
        config()->set([
            'openapi-validator.enable_renderer_for_non_validation_errors' => false,
        ]);

        Route::post('/', fn () => throw new UnauthorizedHttpException(''))->middleware(OpenApiValidator::class);

        $this->json(Request::METHOD_POST, '/', ['hoge' => [1]])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonMissingPath('status')
            ->assertJsonMissingPath('title')
        ;

        Event::assertNotDispatched(RequestValidationFailed::class);
        Event::assertNotDispatched(ResponseValidationFailed::class);
    }
}
