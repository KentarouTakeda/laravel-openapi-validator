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

