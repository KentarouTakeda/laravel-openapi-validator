<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorRendererInterface;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\PathNotFoundException;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ValidateRequestResponse
{
    public function __construct(
        private readonly Config $config,
        private readonly Dispatcher $eventDispatcher,
        private readonly ErrorRendererInterface $errorRenderer,
        private readonly PsrHttpFactory $psrHttpFactory,
    ) {
    }

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(
        Request $request,
        \Closure $next,
        string $provider = null
    ): Response {
        $provider ??= $this->config->getDefaultProviderName();
        $schemaRepository = app()->makeWith(SchemaRepository::class, ['providerName' => $provider]);
        assert($schemaRepository instanceof SchemaRepository);

        $psrRequest = $this->psrHttpFactory->createRequest($request);

        try {
            $operationAddress = $schemaRepository->getRequestValidator()->validate($psrRequest);
        } catch (NoPath $e) {
            if ($this->config->getErrorOnNoPath()) {
                $pathNotFoundException = new PathNotFoundException(request: $psrRequest, previous: $e);

                return $this->renderResponseError(
                    $request,
                    $pathNotFoundException,
                );
            }

            return $next($request);
        } catch (ValidationFailed $validationFailed) {
            return $this->renderRequestError(
                $request,
                $validationFailed,
            );
        }

        $this->dispatchResponseValidation($operationAddress, $schemaRepository);

        return $next($request);
    }

    private function dispatchResponseValidation(OperationAddress $operationAddress, SchemaRepository $schemaRepository): void
    {
        $this->eventDispatcher->listen(RequestHandled::class, function (RequestHandled $event) use ($operationAddress, $schemaRepository) {
            if ($event->response->exception) {
                $response = $this->renderResponseError(
                    $event->request,
                    $event->response->exception,
                );
                $this->overrideResponse($event, $response);

                return;
            }

            $psrResponse = $this->psrHttpFactory->createResponse($event->response);

            try {
                $schemaRepository->getResponseValidator()->validate($operationAddress, $psrResponse);
            } catch (ValidationFailed $validationFailed) {
                $response = $this->renderResponseError(
                    $event->request,
                    $validationFailed,
                );
                $this->overrideResponse($event, $response);

                return;
            }
        });
    }

    private function overrideResponse(RequestHandled $event, Response $response): void
    {
        $event->response->headers = new ResponseHeaderBag();

        $event->response
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->setCache(['no_store' => true])
            ->setContent($response->getContent())
            ->setStatusCode($response->getStatusCode());
    }

    private function renderResponseError(Request $request, \Throwable $error): Response
    {
        return $this->errorRenderer->render(
            $request,
            $error,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->config->getIncludeResErrorInResponse(),
            $this->config->getIncludeTraceInResponse(),
        );
    }

    private function renderRequestError(Request $request, \Throwable $error): Response
    {
        return $this->errorRenderer->render(
            $request,
            $error,
            Response::HTTP_BAD_REQUEST,
            $this->config->getIncludeReqErrorInResponse(),
            false,
        );
    }
}
