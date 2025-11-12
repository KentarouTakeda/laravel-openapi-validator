<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function passesValidationAndReturnSuccessfulResponse(): void
    {
        $this->get('/?status=200')
            ->assertOk();
    }

    #[Test]
    public function failsRequestValidationAndReturnsBadRequestResponse(): void
    {
        $this->get('/')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'title' => 'InvalidQueryArgs',
                'detail' => 'Missing required argument "status" for Request [get /]',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
    }

    #[Test]
    public function failsResponseValidationAndReturnsInternalServerErrorResponse(): void
    {
        $this->get('/?status=201')
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'title' => 'NoResponseCode',
                'detail' => 'OpenAPI spec contains no such operation [/,get,201]',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
    }

    #[Test]
    public function displaySwaggerUi(): void
    {
        $this->get('/openapi-validator/documents/laravel-openapi')
            ->assertSee('<title>Swagger UI</title>', false)
            ->assertOk();
    }
}
