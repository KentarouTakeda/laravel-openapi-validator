<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorRendererInterface;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OpenApiValidator
{
    public function __construct(
        private readonly Config $config,
        private readonly Dispatcher $eventDispatcher,
        private readonly ErrorRendererInterface $errorRenderer,
        private readonly LogManager $logManager,
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
        } catch (NoPath $noPath) {
            if ($this->config->getErrorOnNoPath()) {
                $this->logResponseError($noPath);

                return $this->renderResponseError(
                    $request,
                    $noPath,
                );
            }

            return $next($request);
        } catch (ValidationFailed $validationFailed) {
            $this->logRequestError($validationFailed);

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
            if (in_array($event->response->status(), $this->config->getNonValidatedResponseCodes())) {
                return;
            }

            if ($event->response->exception) {
                $this->logResponseError($event->response->exception);
                if ($this->config->getRespondWithErrorOnResponseValidationFailure()) {
                    $response = $this->renderResponseError(
                        $event->request,
                        $event->response->exception,
                    );
                    $this->overrideResponse($event, $response);
                }

                return;
            }

            $psrResponse = $this->psrHttpFactory->createResponse($event->response);

            try {
                $schemaRepository->getResponseValidator()->validate($operationAddress, $psrResponse);
            } catch (ValidationFailed $validationFailed) {
                $this->logResponseError($validationFailed);
                if ($this->config->getRespondWithErrorOnResponseValidationFailure()) {
                    $response = $this->renderResponseError(
                        $event->request,
                        $validationFailed,
                    );
                    $this->overrideResponse($event, $response);
                }

                return;
            }
        });
    }

    private function logRequestError(\Throwable $error): void
    {
        $logLevel = $this->config->getRequestErrorLogLevel();

        if (null === $logLevel) {
            return;
        }

        $this->logManager->log(
            $logLevel,
            class_basename(static::class).': Request validation failed: '.$error->getMessage(),
            ['error' => $error],
        );
    }

    private function logResponseError(\Throwable $error): void
    {
        $logLevel = $this->config->getResponseErrorLogLevel();

        if (null === $logLevel) {
            return;
        }

        $this->logManager->log(
            $logLevel,
            class_basename(static::class).': Request validation failed: '.$error->getMessage(),
            ['error' => $error],
        );
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
