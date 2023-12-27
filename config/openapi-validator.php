<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default OpenAPI Schema provider.
    |--------------------------------------------------------------------------
    |
    | OpenAPI Schema provider used by default
    |
    */

    'default' => 'laravel-openapi',

    /*
    |--------------------------------------------------------------------------
    | Error on no path
    |--------------------------------------------------------------------------
    |
    | Whether to respond error when the path corresponding
    | to the request is not defined.
    |
    */

    'error_on_no_path' => true,

    /*
    |--------------------------------------------------------------------------
    | Whether to include a request validation error pointer in the response
    |--------------------------------------------------------------------------
    */

    'include_req_error_in_response' => true,

    /*
    |--------------------------------------------------------------------------
    | Whether to include a response validation error pointer in the response
    |--------------------------------------------------------------------------
    */

    'include_res_error_in_response' => true,

    /*
    |--------------------------------------------------------------------------
    | Whether to include a stack trace in the response
    |--------------------------------------------------------------------------
    */

    'include_trace_in_response' => true,

    /*
    |--------------------------------------------------------------------------
    | Default OpenAPI Schema provider.
    |--------------------------------------------------------------------------
    |
    | * `driver`: currently only supports `laravel-openapi`
    | * `collection`: Name of schema to use
    |
    */

    'providers' => [
        'laravel-openapi' => [
            'driver' => 'laravel-openapi',
            'collection' => 'default',
        ],
    ],
];
