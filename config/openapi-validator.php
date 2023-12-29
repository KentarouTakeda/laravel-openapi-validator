<?php

use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\L5SwaggerResolver;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\LaravelOpenApiResolver;

return [
    /*
    |--------------------------------------------------------------------------
    | Default OpenAPI Schema provider.
    |--------------------------------------------------------------------------
    |
    | OpenAPI Schema provider used by default
    |
    */

    'default' => (string) env(
        'OPENAPI_VALIDATOR_PROVIDER',
        'laravel-openapi'
    ),

    /*
    |--------------------------------------------------------------------------
    | Error on no path
    |--------------------------------------------------------------------------
    |
    | Whether to respond error when the path corresponding
    | to the request is not defined.
    |
    */

    'error_on_no_path' => (bool) env(
        'OPENAPI_VALIDATOR_ERROR_ON_NO_PATH',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Whether to include a request validation error pointer in the response
    |--------------------------------------------------------------------------
    */

    'include_req_error_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_REQ_ERROR_IN_RESPONSE',
        true
    ),

    /*
    |--------------------------------------------------------------------------
    | Whether to include a response validation error pointer in the response
    |--------------------------------------------------------------------------
    */

    'include_res_error_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_RES_ERROR_IN_RESPONSE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | Whether to include a stack trace in the response
    |--------------------------------------------------------------------------
    */

    'include_trace_in_response' => (bool) env(
        'OPENAPI_VALIDATOR_INCLUDE_TRACE_IN_RESPONSE',
        env('APP_DEBUG', false),
    ),

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Schema providers.
    |--------------------------------------------------------------------------
    |
    | By default, `default` is used. If your system handles multiple OpenAPI
    | specifications, you can specify which one to use with the middleware
    | parameter `provider`.
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
];
