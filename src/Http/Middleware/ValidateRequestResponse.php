<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use KentarouTakeda\Laravel\OpenApiValidator\Contracts\SchemaRepository;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\ExceptionHandler;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\PathNotFoundException;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ValidateRequestResponse
{
    public function __construct(
        private readonly Dispatcher $eventDispatcher,
        private readonly ExceptionHandler $exceptionHandler,
        private readonly PsrHttpFactory $psrHttpFactory,
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
            return $this->exceptionHandler->renderWithStatusCode($request, $e, Response::HTTP_BAD_REQUEST);
        }

        $this->eventDispatcher->listen(RequestHandled::class, function (RequestHandled $event) use ($operationAddress) {
            $response = $event->response;
            $exception = $response->exception;

            if ($exception) {
                $response = $this->exceptionHandler->renderWithStatusCode($event->request, $exception, Response::HTTP_INTERNAL_SERVER_ERROR);

                return $this->overrideResponse($event, $response);
            }

            $psrResponse = $this->psrHttpFactory->createResponse($response);

            try {
                $this->schemaRepository->getResponseValidator()->validate($operationAddress, $psrResponse);
            } catch (ValidationFailed $e) {
                $response = $this->exceptionHandler->renderWithStatusCode($event->request, $e, Response::HTTP_INTERNAL_SERVER_ERROR);

                return $this->overrideResponse($event, $response);
            }
        });

        $response = $next($request);

        return $response;
    }

    private function overrideResponse(RequestHandled $event, Response $response): void
    {
        $event->response->headers = new ResponseHeaderBag();

        $event->response
            ->setCache(['no_store' => true])
            ->setContent($response->getContent())
            ->setStatusCode($response->getStatusCode());
    }
}
