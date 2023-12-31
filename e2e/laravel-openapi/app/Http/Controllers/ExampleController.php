<?php

namespace App\Http\Controllers;

use App\OpenApi\Parameters\ExampleQueryParameters;
use App\OpenApi\Responses\ExampleResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class ExampleController
{
    /**
     * Example summary
     *
     * Example description
     */
    #[OpenApi\Operation]
    #[OpenApi\Parameters(factory: ExampleQueryParameters::class)]
    #[OpenApi\Response(factory: ExampleResponse::class)]
    public function __invoke(Request $request): JsonResponse
    {
        return response()
            ->json([], $request->query('status'));
    }
}
