<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Renderer;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use KentarouTakeda\Laravel\OpenApiValidator\ErrorRendererInterface;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
    ) {
    }

    public function render(
        Request $request,
        \Throwable $error,
        int $status,
        bool $includePointer,
        bool $includeTrace,
    ): Response {
        $current = $this->prepareException($error);
        if ($current instanceof HttpException) {
            $status = $current->getStatusCode();
        }

        $json = [
            'title' => class_basename($current),
            'detail' => $current->getMessage() ?: null,
            'status' => $status,
        ];

        while ($includePointer && $current) {
            $current = $current->getPrevious();

            if ($current instanceof SchemaMismatch) {
                $json['pointer'] = $current->dataBreadCrumb()?->buildChain() ?: null;
                break;
            }
        }

        return $this->responseFactory->json($json, $status)->setStatusCode($status);
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
