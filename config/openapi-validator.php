<?php

use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\L5SwaggerResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;
use Symfony\Component\HttpFoundation\Response;

return [
    /*
    |--------------------------------------------------------------------------
    | Default OpenAPI Schema Provider
    |--------------------------------------------------------------------------
    |
    | This setting determines the default OpenAPI schema provider to be used. 
    | The default provider is `laravel-openapi` and can be customized through 
    | the 'OPENAPI_VALIDATOR_PROVIDER' environment variable.
    |
    */

    'default' => (string) env(
        'OPENAPI_VALIDATOR_PROVIDER',
        'laravel-openapi'
    ),

    /*
    |--------------------------------------------------------------------------
    | Respond with Error on Response Validation Failure
    |--------------------------------------------------------------------------
    |
    | This setting determines whether the OpenAPI validator should respond with 
    | an error when it fails to validate a response.
    |
    */

    'respond_error_on_res_validation_failure' => (bool) env(
        'OPENAPI_VALIDATOR_RESPOND_WITH_ERROR_ON_RESPONSE_VALIDATION_FAILURE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Error on No Path
    |--------------------------------------------------------------------------
    |
    | This setting determines whether to respond with an error when the path 
    | corresponding to the request is not defined in the OpenAPI schema. 
    | The default behavior is according to `APP_DEBUG` and can be customized 
    | through the 'OPENAPI_VALIDATOR_ERROR_ON_NO_PATH' environment variable.
    |
    */

    'error_on_no_path' => (bool) env(
        'OPENAPI_VALIDATOR_ERROR_ON_NO_PATH',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Include Response Validation Error Detail in Response
    |--------------------------------------------------------------------------
    |
    | This setting determines whether the OpenAPI validator should include 
    | details of response validation errors in the response. The default value 
    | is `true` and can be customized through the
    | 'OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE' environment variable.
    |
    */

    'include_req_error_detail_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_REQ_ERROR_IN_RESPONSE',
        true
    ),

    /*
    |--------------------------------------------------------------------------
    | Include Request Validation Error Detail in Response
    |--------------------------------------------------------------------------
    |
    | This setting determines whether the OpenAPI validator should include 
    | details of response validation errors in the response. The default 
    | behavior is according to `APP`DEBUG`  and can be customized through the
    | 'OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE' environment variable.
    |
    */

    'include_res_error_detail_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Include Trace Information in Response
    |--------------------------------------------------------------------------
    |
    | This setting determines whether the OpenAPI validator should include 
    | trace information in the error response. The default behavior is
    | according to `APP`DEBUG` and can be customized through the 
    | 'OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE' environment variable.
    |
    */

    'include_trace_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Include Original Response in Error Response
    |--------------------------------------------------------------------------
    |
    | This setting determines whether the OpenAPI validator should include 
    | the original response in the error response. The default behavior is
    | according to `APP_DEBUG` and can be customized through the 
    | 'OPENAPI_VALIDATOR_INCLUDE_ORIGINAL_RES_IN_RESPONSE' environment variable.
    |
    */

    'include_original_res_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_ORIGINAL_RES_IN_RESPONSE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Request Error Log Level
    |--------------------------------------------------------------------------
    |
    | This setting determines the log level for request errors in the OpenAPI
    | validator. The default level is 'info'. This can be customized through
    | the 'OPENAPI_VALIDATOR_REQUEST_ERROR_LOG_LEVEL' environment variable.
    |
    | If set to null, no logging will occur.
    |
    | Log levels:
    |   emergency, alert, critical, error, warning, notice, info, debug
    |
    */

    'req_error_log_level' => (string) env(
        'OPENAPI_VALIDATOR_REQUEST_ERROR_LOG_LEVEL',
        'info'
    ),

    /*
    |--------------------------------------------------------------------------
    | Response Error Log Level
    |--------------------------------------------------------------------------
    |
    | This setting determines the log level for response errors in the OpenAPI
    | validator. The default level is 'warning'. This can be customized through
    | the 'OPENAPI_VALIDATOR_RESPONSE_ERROR_LOG_LEVEL' environment variable.
    |
    | If set to null, no logging will occur.
    |
    | Log levels:
    |   emergency, alert, critical, error, warning, notice, info, debug
    |
    */
    'res_error_log_level' => (string) env(
        'OPENAPI_VALIDATOR_RESPONSE_ERROR_LOG_LEVEL',
        'warning'
    ),

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Schema providers.
    |--------------------------------------------------------------------------
    |
    | By default, `laravel-openapi` is used. If your system handles multiple
    | OpenAPI specifications, you can specify which one to use with the
    | middleware parameter `provider`.
    |
    | See the sample below for supported resolvers and settings.
    |
    */

    'providers' => [
        /*
        | Laravel OpenAPI
        |
        | https://github.com/vyuldashev/laravel-openapi
        */

        'laravel-openapi' => [
            'resolver' => LaravelOpenApiResolver::class,
            'collection' => (string) env(
                'OPENAPI_VALIDATOR_COLLECTION_NAME',
                'default'
            ),
        ],

        /*
        | L5-Swagger
        |
        | https://github.com/DarkaOnLine/L5-Swagger
        */

        'l5-swagger' => [
            'resolver' => L5SwaggerResolver::class,
            'documentation' => (string) env(
                'OPENAPI_VALIDATOR_COLLECTION_NAME',
                'default'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Swagger UI
    |--------------------------------------------------------------------------
    |
    | This setting controls whether the Swagger UI is enabled or not. It can be 
    | set via the 'OPENAPI_VALIDATOR_IS_SWAGGER_UI_ENABLED' environment variable. 
    | The default behavior is according to `APP`DEBUG`
    |
    */

    'is_swagger_ui_enabled' => (bool) env(
        'OPENAPI_VALIDATOR_IS_SWAGGER_UI_ENABLED',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Internal Settings
    |--------------------------------------------------------------------------
    |
    | These are internal settings for the OpenAPI validator. You can change 
    | these if necessary.
    |
    */

    'cache_directory' => storage_path('openapi-validator'),

    'non_validated_response_codes' => [
        Response::HTTP_MOVED_PERMANENTLY,
        Response::HTTP_FOUND,
        Response::HTTP_SEE_OTHER,
        Response::HTTP_NOT_MODIFIED,
        Response::HTTP_TEMPORARY_REDIRECT,
        Response::HTTP_PERMANENTLY_REDIRECT,
    ],
];
