<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorRendererInterface;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorType;
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
     * @param string|null $provider if not specified, the default provider will be used
     * @param bool $skipResponseValidation if true, response validation will be skipped
     */
    public static function config(
        string $provider = null,
        bool $skipResponseValidation = false
    ): string {
        return static::class.':'.implode(',', [
            $provider ?? '',
            $skipResponseValidation ? '1' : '0',
        ]);
    }

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(
        Request $request,
        \Closure $next,
        string $provider = '',
        bool $skipResponseValidation = false,
    ): Response {
        $provider = $provider ?: $this->config->getDefaultProviderName();
        $schemaRepository = app()->makeWith(SchemaRepository::class, ['providerName' => $provider]);
        assert($schemaRepository instanceof SchemaRepository);

        $psrRequest = $this->psrHttpFactory->createRequest($request);

        try {
            $operationAddress = $schemaRepository->getRequestValidator()->validate($psrRequest);
        } catch (NoPath $noPath) {
            if ($this->config->getErrorOnNoPath() && !$skipResponseValidation) {
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

        if (!$skipResponseValidation) {
            $this->dispatchResponseValidation($operationAddress, $schemaRepository);
        }

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
                if ($this->config->getRespondErrorOnResValidationFailure()) {
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
                if ($this->config->getRespondErrorOnResValidationFailure()) {
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
        $this->logManager->log(
            $this->config->getReqErrorLogLevel(),
            class_basename(static::class).': Request validation failed: '.$error->getMessage(),
            ['error' => $error],
        );
    }

    private function logResponseError(\Throwable $error): void
    {
        $this->logManager->log(
            $this->config->getResErrorLogLevel(),
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
            ErrorType::Response
        );
    }

    private function renderRequestError(Request $request, \Throwable $error): Response
    {
        return $this->errorRenderer->render(
            $request,
            $error,
            ErrorType::Request,
        );
    }
}
