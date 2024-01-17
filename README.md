# Laravel OpenAPI Validator

Request and response validators based on the OpenAPI Specification.

## Summary

* Validate any request and response with a pre-prepared OpenAPI Spec.
* Automatically load specs from [Laravel OpenAPI](https://vyuldashev.github.io/laravel-openapi/) or [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger/wiki).
* You can also load your own specs without using these libraries.
* You can customize validation and error logging behavior on a per-route or application-wide basis.
* Can display [Swagger UI](https://swagger.io/tools/swagger-ui/). You can view the documentation and test the API.


## Requirements

* PHP 8.1 or higher
* Laravel 9.0 or higher

## Installation

You can install the package via composer:

```bash
composer require kentaroutakeda/laravel-openapi-validator
```

## Usage

1. Configure OpenAPI Specification

   If you're using Laravel OpenAPI, you don't need to do anything.

   For L5 Swagger, the following settings are required:

   ```ini
   # .env
   OPENAPI_VALIDATOR_PROVIDER="l5-swagger"
   ```

   How to load your own schema without using these packages will be
   explained later.

2. Register Middleware

   ```php
   Route::get('/example', ExampleController::class)
       ->middleware(OpenApiValidator::class); // <- Add this line
   ```
   
   Routes with this setting will be validated for all requests including
   Feature Tests, and depending on the settings, responses as well.

   *NOTE:*  
   This repository's [./e2e](./e2e) directory contains working examples
   for e2e testing. You can see middleware configuration examples in
   Routing, and actual validations and failures in Tests.

3. (Optional) Customize Middleware

   If necessary, you can change Middleware behavior for each route.
   
   ```php
   Route::get('/', ExampleController::class)
     ->middleware(OpenApiValidator::config(
       provider: 'admin-api', // <- Use spec other than default
       skipResponseValidation: true // <- Skip Response Validation
     ));
   ```

   *NOTE:*  
   Response validation for large amounts of data can take a long time.
   It would be a good idea to switch on/off validation depending on the
   route and `APP_*` environment variables.

4. Deployment

   When deploying your application to production, you should make sure
   that you run the `openapi-validator:cache` Artisan command
   during your deployment process:
   
   ```bash
   php artisan openapi-validator:cache
   ```
   
   This command caches the OpenAPI Spec defined in your application.
   If you change the definition for development, you need to
   clear it as follows:
   
   ```bash
   php artisan openapi-validator:clear
   ```

## (Optional) Swagger UI support

You can view the Swagger UI just by installing the package. No additional
configuration is required.

1. Install package.

   ```bash
   composer require swagger-api/swagger-ui
   ```

2. Display `APP_URL/openapi-validator/documents` in browser.

   ```bash
   open http://localhost:8000/openapi-validator/documents
   ```

*NOTE:*  
By default, the Swagger UI can only be displayed when `APP_DEBUG` is enabled.

## (Optional) Customization

### Publish Configuration

You can publish the config file to change behavior.

```bash
php artisan openapi-validator:publish
```

Alternatively, most settings can be changed using environment variables.
Check the comments in [config/openapi-validator.php](config/openapi-validator.php) for details.

### Your own schema providers

1. If you want to use your own schema providers, first publish the config.

2. Next, implement a class to retrieve the schema.

   ```php
   class MyResolver implements ResolverInterface
   {
     public function getJson(array $options): string
     {
       // This example assumes that the schema exists in the root directory.
       return File::get(base_path('openapi.json'));
     }
   }
   ```

3. Finally, set it in your config.

   ```php
   return [
     // Set the provider name.
     'default' => 'my-resolver',

     'providers' => [
       // Set the provider name you created.
       'my-resolver' => [
         // Specify the class you created in the `resolver` parameter.
         'resolver' => MyResolver::class,
       ],
     ],
   ];
   ```

### Error responses and customization

By default, it is formatted according to
[RFC 7807 - Problem Details for HTTP APIs](https://datatracker.ietf.org/doc/html/rfc7807).

Validation errors, stack traces and original response
can also be included depending on your settings.
For example, it might look like this:

```json
{
  "title": "NoResponseCode",
  "detail": "OpenAPI spec contains no such operation [/,get,201]", // Error reason
  "status": 500, // Same as HTTP response code
  "originalResponse": { "status": "201" }, // Original response before validation
  "trace": [
    { "error": "...", "message": "...", "file": "...", "line": 42 },
    { "...": "..." }
  ]
}
```

Here's how to change to a different format:

1. First, implement a class to generate a response. For example:

   ```php
   class MyErrorRenderer implements ErrorRendererInterface
   {
     public function render(
       Request $request,
       \Throwable $error,
       ErrorType $errorType,
     ): Response {
       return new Response(
         match ($errorType) {
           ErrorType::Request => "Request Error: " . $error->getMessage(),
           ErrorType::Response => "Response Error: " . $error->getMessage(),
         }
       );
     }
   }
   ```

2. Next, register the class to the service container.

   ```php
   // AppServiceProvider.php
   
   public function register(): void
   {
     $this->app->bind(
       ErrorRendererInterface::class,
       MyErrorRenderer::class
     );
   }
   ```

### Events

If a validation error occurs, an event will be fired
depending on the type of error.

* `ValidationFailedInterface` - All errors
  * `RequestValidationFailed` - Request validation error
  * `ResponseValidationFailed` - Response validation error

## Contributing and Development

```bash
# Clone this repository and move to the directory.
git clone https://github.com/KentarouTakeda/laravel-openapi-validator.git
cd laravel-openapi-validator

# Install dependencies.
composer install

# (Optional) Install tools: The commit hook automatically formats the code.
npm install

# Run tests.
vendor/bin/phpunit
```

## License

Laravel OpenAPI Validator is open-sourced software licensed under the
[MIT license](https://opensource.org/licenses/MIT).
