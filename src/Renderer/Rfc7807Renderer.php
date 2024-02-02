<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Renderer;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorRendererInterface;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Rfc7807Renderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly Config $config,
    ) {
    }

    public function render(
        \Throwable $error,
        Request $request,
        ?Response $response = null,
    ): Response {
        $error = $this->prepareException($error);

        $status = $error instanceof HttpException ?
            $error->getStatusCode() :
            ($response ?
                Response::HTTP_INTERNAL_SERVER_ERROR :
                Response::HTTP_BAD_REQUEST);

        $json = [
            'title' => class_basename($error),
            'detail' => $error->getMessage() ?: null,
            'status' => $status,
        ];

        $shouldIncludePointer = $response ?
            $this->config->getIncludeResErrorDetailInResponse() :
            $this->config->getIncludeReqErrorDetailInResponse();

        if ($shouldIncludePointer) {
            $schemaMismatch = $this->findSchemaMismatch($error);

            if ($schemaMismatch) {
                $json['pointer'] = $schemaMismatch->dataBreadCrumb()?->buildChain() ?: null;
                $json['detail'] = $schemaMismatch->getMessage() ?: $error->getMessage() ?: null;
            }
        }

        if ($response && $this->config->getIncludeOriginalResInResponse()) {
            $json['originalResponse'] = $this->extractResponseBody($response);
        }

        if ($this->config->getIncludeTraceInResponse()) {
            $json['trace'] = $this->extractTrace($error);
        }

        return $this->responseFactory->json(
            $json,
            $status,
            options: config('app.debug') ?
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE :
                0
        );
    }

    private function findSchemaMismatch(\Throwable $error): ?SchemaMismatch
    {
        while ($error) {
            $error = $error->getPrevious();

            if ($error instanceof SchemaMismatch) {
                return $error;
            }
        }

        return null;
    }

    private function extractResponseBody(Response $response): mixed
    {
        $content = $response->getContent();

        if (!is_string($content)) {
            return $content;
        }

        json_decode('null');
        $object = json_decode($content, true);

        return json_last_error() ? $content : $object;
    }

    /**
     * @return array<int, array<string, int|string|object>>
     */
    private function extractTrace(\Throwable $error): array
    {
        $records = [];
        while ($error) {
            $records[] = [
                'error' => get_class($error),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ];

            foreach ($error->getTrace() as $record) {
                unset($record['args']);
                $records[] = $record;
            }

            $error = $error->getPrevious();
        }

        return $records;
    }

    /**
     * Same implementation as error conversion inside Laravel Framework
     * 
     * @see https://laravel.com/api/10.x/Illuminate/Foundation/Exceptions/Handler.html#method_prepareException
     */
    private function prepareException(\Throwable $e): \Throwable
    {
        return match (true) {
            $e instanceof BackedEnumCaseNotFoundException => new NotFoundHttpException($e->getMessage(), $e),
            $e instanceof ModelNotFoundException => new NotFoundHttpException($e->getMessage(), $e),
            $e instanceof AuthorizationException && $e->hasStatus() => new HttpException(
                // @phpstan-ignore-next-line
                $e->status(), $e->response()?->message() ?: (Response::$statusTexts[$e->status()] ?? 'Whoops, looks like something went wrong.'), $e
            ),
            $e instanceof AuthorizationException && !$e->hasStatus() => new AccessDeniedHttpException($e->getMessage(), $e),
            $e instanceof TokenMismatchException => new HttpException(419, $e->getMessage(), $e),
            $e instanceof SuspiciousOperationException => new NotFoundHttpException('Bad hostname provided.', $e),
            $e instanceof RecordsNotFoundException => new NotFoundHttpException('Not found.', $e),
            default => $e,
        };
    }
}
