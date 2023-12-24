<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Contracts\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\PathNotFoundException;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\ValidateRequestResponse;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ValidateRequestResponseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    private function mockValidator(): ValidatorBuilder
    {
        $json = json_encode([
            'paths' => [
                '/' => [
                    'post' => [
                        'responses' => [
                            '200' => [],
                        ],
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
        $this->mock(SchemaRepository::class, fn (MockInterface $mock) => $mock->allows([
            'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
        ]));

        Route::post('/', fn () => 'Hello')->middleware(ValidateRequestResponse::class);

        $this->json(
            Request::METHOD_POST,
            '/?foo=1', ['hoge' => [1]]
        )->assertOk();
    }

    #[Test]
    public function throwsPathNotFoundException(): void
    {
        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path not found: GET /not-found');

        $this->mock(SchemaRepository::class, fn (MockInterface $mock) => $mock->allows([
            'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
        ]));

        Route::get('/not-found', fn () => 'Hello')->middleware(ValidateRequestResponse::class);

        $this->get('/not-found')
            ->assertOk();
    }

    #[Test]
    public function returnsBadRequest(): void
    {
        $this->mock(SchemaRepository::class, fn (MockInterface $mock) => $mock->allows([
            'getRequestValidator' => $this->mockValidator()->getRequestValidator(),
        ]));

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
}
