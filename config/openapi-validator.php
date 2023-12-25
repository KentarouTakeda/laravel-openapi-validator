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
    | Include Breadcrumbs when a request error occurs
    |--------------------------------------------------------------------------
    |
    | Whether to include breadcrumb in the response when a request error occurs
    |
    */

    'include_breadcrumbs_in_request_error' => true,

    /*
    |--------------------------------------------------------------------------
    | Include Breadcrumbs when a response error occurs
    |--------------------------------------------------------------------------
    |
    | Whether to include breadcrumb in the response when a response error occurs
    |
    */

    'include_breadcrumbs_in_response_error' => true,

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
