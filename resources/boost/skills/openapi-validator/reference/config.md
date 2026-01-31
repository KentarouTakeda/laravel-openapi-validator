# OpenAPI Validator Configuration Reference

## Environment Variables

| Environment Variable | Default | Description |
|---------|-----------|------|
| `OPENAPI_VALIDATOR_PROVIDER` | `laravel-openapi` | Default Schema Provider name |
| `OPENAPI_VALIDATOR_VALIDATE_ERROR_RESPONSES` | `true` | Whether to validate error responses |
| `OPENAPI_VALIDATOR_RESPOND_WITH_ERROR_ON_RESPONSE_VALIDATION_FAILURE` | `APP_DEBUG` | Whether to respond with error on response validation failure |
| `OPENAPI_VALIDATOR_ERROR_ON_NO_PATH` | `APP_DEBUG` | Whether to respond with error when path is not defined |
| `OPENAPI_VALIDATOR_ENABLE_RENDERER_FOR_NON_VALIDATION_ERRORS` | `false` | Whether to render non-validation errors with this package's renderer |
| `OPENAPI_VALIDATOR_INCLUDE_REQ_ERROR_IN_RESPONSE` | `true` | Whether to include request error details in response |
| `OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE` | `APP_DEBUG` | Whether to include response error details in response |
| `OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE` | `APP_DEBUG` | Whether to include stack trace in response |
| `OPENAPI_VALIDATOR_INCLUDE_ORIGINAL_RES_IN_RESPONSE` | `APP_DEBUG` | Whether to include original response in error response |
| `OPENAPI_VALIDATOR_REQUEST_ERROR_LOG_LEVEL` | `info` | Log level for request errors |
| `OPENAPI_VALIDATOR_RESPONSE_ERROR_LOG_LEVEL` | `warning` | Log level for response errors |
| `OPENAPI_VALIDATOR_IS_SWAGGER_UI_ENABLED` | `APP_DEBUG` | Whether to enable Swagger UI |

## Provider Settings

### Laravel OpenAPI

```php
'laravel-openapi' => [
    'resolver' => LaravelOpenApiResolver::class,
    'collection' => 'default',
],
```

### L5-Swagger

```php
'l5-swagger' => [
    'resolver' => L5SwaggerResolver::class,
    'documentation' => 'default',
],
```

### Your own provider

```php
'my-resolver' => [
    'resolver' => MyResolver::class,
    // Any additional keys are passed as the array argument to getJson()
],
```

## Middleware Options

```php
// Default
Route::middleware(OpenApiValidator::class);

// Customization example
Route::middleware(OpenApiValidator::config(
    // Provider name from config
    provider: 'my-resolver',
    // Skip response validation in production
    skipResponseValidation: app()->isProduction(),
));
```

## Events

Events dispatched on validation errors:

- `RequestValidationFailed` - On request validation failure
- `ResponseValidationFailed` - On response validation failure

Both events implement `ValidationFailedInterface`.

```php
class MyListener
{
    public function handle(RequestValidationFailed $event): void
    {
        // $event->getThrowable() - Validation exception
        // $event->getRequest() - HTTP request
    }
}
```

## Error Response Customization

To change from RFC 7807 format, implement `ErrorRendererInterface` and bind it in the container:

```php
class MyErrorRenderer implements ErrorRendererInterface
{
    public function render(
        \Throwable $error,
        Request $request,
        ?Response $response = null,
    ): Response {
        return response()->json([
            'error' => $error->getMessage(),
        ], $response ? 500 : 400);
    }
}
```

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->bind(ErrorRendererInterface::class, MyErrorRenderer::class);
}
```
