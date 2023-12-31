<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Example API",
    version: "1.0.0",
)]
class ExampleController
{
    #[OA\Get(
        path: '/',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        return response()
            ->json([], $request->query('status'));
    }
}
