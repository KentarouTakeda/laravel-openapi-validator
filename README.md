# Laravel OpenAPI Validator

Request and response validators based on the OpenAPI Specification.

Supports
[Laravel OpenAPI](https://vyuldashev.github.io/laravel-openapi/)
and [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger/wiki),
and you can also use your own schema.

## Installation

You can install the package via composer:

```bash
composer require kentaroutakeda/laravel-openapi-validator
```

> [!NOTE]  
> 
> Depending on the configuration of your Laravel Application,
> you may need to downgrade some of the following dependencies:
>
> ```bash
> composer require kentaroutakeda/laravel-openapi-validator --no-update
> composer update kentaroutakeda/laravel-openapi-validator --with-all-dependencies
> ```
>
> Please see
> [here](https://github.com/thephpleague/openapi-psr7-validator/pull/213)
> for details.

## Usage

### Configure OpenAPI Specification

If you're using Laravel OpenAPI, you don't need to do anything.

For L5 Swagger, the following settings are required:

```ini
# .env
OPENAPI_VALIDATOR_PROVIDER="l5-swagger"
```

How to load your own schema without using these packages will be
explained later.

### Register Middleware

```php
Route::get('/example', ExampleController::class)
    ->middleware(OpenApiValidator::class); // <- Add this line
```

> [!NOTE]  
> This repository's ./e2e directory contains working examples
> for e2e testing.

### Customize Middleware

If necessary, you can change Middleware behavior for each Route.

```php
Route::get('/', ExampleController::class)
  ->middleware(OpenApiValidator::config(
    provider: 'admin-api', // <- Use spec other than default
    skipResponseValidation: true // <- Skip Response Validation
  ));
```

> [!NOTE]  
> Response validation for large amounts of data can take a long time.
> It would be a good idea to switch on/off validation depending on the
> route and `APP_*` environment variables.

### Deployment

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

## Customization

### Default OpenAPI Schema Provider

This setting determines the default OpenAPI schema provider to be used. 
The default provider is `laravel-openapi` and can be customized through 
the `OPENAPI_VALIDATOR_PROVIDER` environment variable.

### Respond with Error on Response Validation Failure

This setting determines whether the OpenAPI validator should respond with 
an error when it fails to validate a response.

### Error on No Path

This setting determines whether to respond with an error when the path 
corresponding to the request is not defined in the OpenAPI schema. 
The default behavior is according to `APP_DEBUG` and can be customized 
through the `OPENAPI_VALIDATOR_ERROR_ON_NO_PATH` environment variable.

### Include Response Validation Error Detail in Response

This setting determines whether the OpenAPI validator should include 
details of response validation errors in the response. The default value 
is `true` and can be customized through the
`OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE` environment variable.

### Include Request Validation Error Detail in Response

This setting determines whether the OpenAPI validator should include 
details of response validation errors in the response. The default 
behavior is according to `APP`DEBUG`  and can be customized through the
`OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE` environment variable.

### Include Trace Information in Response

This setting determines whether the OpenAPI validator should include 
trace information in the error response. The default behavior is
according to `APP`DEBUG` and can be customized through the 
`OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE` environment variable.

### Request Error Log Level

This setting determines the log level for request errors in the OpenAPI
validator. The default level is `info`. This can be customized through
the `OPENAPI_VALIDATOR_REQUEST_ERROR_LOG_LEVEL` environment variable.

> [!NOTE]  
> Log levels:  
> emergency, alert, critical, error, warning, notice, info, debug  

### Response Error Log Level

This setting determines the log level for response errors in the OpenAPI
validator. The default level is `warning`. This can be customized through
the `OPENAPI_VALIDATOR_RESPONSE_ERROR_LOG_LEVEL` environment variable.

> [!NOTE]  
> Log levels:  
> emergency, alert, critical, error, warning, notice, info, debug  

### OpenAPI Schema providers.

By default, `laravel-openapi` is used. If your system handles multiple
OpenAPI specifications, you can specify which one to use with the
middleware parameter `provider`.

### Multiple Documents

laravel-openapi or l5-swagger supports managing multiple documents.
OpenAPI validator uses those `default` by default. This behavior can be
customized through the environment variable
`OPENAPI_VALIDATOR_COLLECTION_NAME`.

