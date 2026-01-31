---
name: openapi-validator
description: >-
  Assists with request/response validation setup and testing based on OpenAPI schema.
  Activated when creating/modifying API endpoints/routes, writing tests, configuring Schema Providers, or defining schemas.
---

@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
$isLaravelOpenApi = \KentarouTakeda\Laravel\OpenApiValidator\isLaravelOpenAPIInstalled();
$isL5Swagger = \KentarouTakeda\Laravel\OpenApiValidator\isl5SwaggerInstalled();
@endphp

# OpenAPI Validator Detailed Guide

## Middleware Setup

```php
Route::get('/example', ExampleController::class)
    ->middleware(OpenApiValidator::class);
```

See `reference/config.md` for customization options.

## Writing Tests

With `skipResponseValidation` disabled (default), normal-case tests alone are sufficient. Passing tests guarantees schema conformance. When `skipResponseValidation: true`, write separate response validation tests.

```php
class UserControllerTest extends TestCase
{
    #[Test]
    public function it_can_retrieve_user_information(): void
    {
        $user = User::factory()->create();

        $this->getJson("/users/{$user->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
    }
}
```

@if($isL5Swagger)
## Schema Provider: L5-Swagger

Uses L5-Swagger. Define schemas by writing OpenAPI annotations in controllers.

### Annotation Placement

Placement principles:

- Operation definitions (`OA\Get`, `OA\Post`, etc.), `OA\RequestBody`, and `OA\Response` go on controller methods. Reference response body schemas via `ref`
- Response data `OA\Schema` goes on JsonResource (API resource classes). Follow project-specific conventions if a custom transformation layer exists

### API Resource Class Example

```php
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
    ]
)]
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### Controller Example

```php
use OpenApi\Attributes as OA;

class UserController
{
    #[OA\Get(
        path: '/users/{id}',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User information',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    ]
                )
            ),
        ]
    )]
    public function show(int $id): UserResource
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }
}
```

### .env Configuration

```ini
OPENAPI_VALIDATOR_PROVIDER="l5-swagger"
```
@endif

@if($isLaravelOpenApi)
## Schema Provider: Laravel OpenAPI

Uses Laravel OpenAPI. Define schemas with attributes and factory classes.

### Controller Example

```php
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class UserController
{
    #[OpenApi\Operation]
    #[OpenApi\Parameters(factory: UserShowParameters::class)]
    #[OpenApi\Response(factory: UserResponse::class)]
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }
}
```

### Parameter Factory Example

```php
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class UserShowParameters extends ParametersFactory
{
    public function build(): array
    {
        return [
            Parameter::path()
                ->name('id')
                ->required()
                ->schema(Schema::integer()),
        ];
    }
}
```

### Response Factory Example

```php
class UserResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::ok()
            ->description('User information')
            ->content(
                MediaType::json()->schema(
                    Schema::object()->properties(
                        Schema::integer('id'),
                        Schema::string('name'),
                        Schema::string('email'),
                    )
                )
            );
    }
}
```

### .env Configuration

```ini
OPENAPI_VALIDATOR_PROVIDER="laravel-openapi"
```
@endif

@if(!$isLaravelOpenApi && !$isL5Swagger)
## Schema Provider: Custom Resolver

Uses a custom Schema Provider. Implement `ResolverInterface` to provide the schema.

### Resolver Implementation

```php
class MyResolver implements ResolverInterface
{
    public function getJson(array $options): string
    {
        return File::get(base_path('openapi.json'));
    }
}
```

### Configuration (config/openapi-validator.php)

Publish the config file and register the resolver:

```bash
{{ $assist->artisanCommand('openapi-validator:publish') }}
```

```php
return [
    'default' => 'my-resolver',

    'providers' => [
        'my-resolver' => [
            'resolver' => MyResolver::class,
            // Any additional keys are passed as the array argument to getJson()
        ],
    ],
];
```
@endif

## Caching and Deployment

Schema caching is integrated with Laravel's optimize commands.

```bash
# Production deployment (includes OpenAPI schema caching)
{{ $assist->artisanCommand('optimize') }}

# Clear cache (development)
{{ $assist->artisanCommand('optimize:clear') }}
```

Individual commands:

```bash
{{ $assist->artisanCommand('openapi-validator:cache') }}
{{ $assist->artisanCommand('openapi-validator:clear') }}
```

## Error Response Format

Validation errors follow [RFC 7807](https://datatracker.ietf.org/doc/html/rfc7807) format:

```json
{
    "title": "InvalidQueryArgs",
    "detail": "Missing required argument \"status\" for Request [get /]",
    "status": 400
}
```

See `reference/config.md` for detailed configuration.
