<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use KentarouTakeda\Laravel\OpenApiValidator\Contracts\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\PathNotFoundException;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class ValidateRequestResponse
{
    public function __construct(
        private readonly PsrHttpFactory $psrHttpFactory,
        private readonly ResponseFactory $responseFactory,
        private readonly SchemaRepository $schemaRepository,
    ) {
    }

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $psrRequest = $this->psrHttpFactory->createRequest($request);

        try {
            $operationAddress = $this->schemaRepository->getRequestValidator()->validate($psrRequest);
        } catch (NoPath $e) {
            throw new PathNotFoundException(request: $psrRequest, previous: $e);
        } catch (ValidationFailed $e) {
            return $this->makeRequestValidationFailedResponse($e);
        }

        $response = $next($request);

        return $response;
    }

    private function makeRequestValidationFailedResponse(ValidationFailed $e): Response
    {
        $json = [
            'title' => class_basename($e),
            'detail' => $e->getMessage() ?: null,
            'status' => Response::HTTP_BAD_REQUEST,
        ];

        $current = $e;
        while ($current) {
            $current = $current->getPrevious();

            if ($current instanceof SchemaMismatch) {
                $json['breadcrumb'] = $current->dataBreadCrumb()?->buildChain() ?: null;
                break;
            }
        }

        return $this->responseFactory->json($json, Response::HTTP_BAD_REQUEST);
    }
}
