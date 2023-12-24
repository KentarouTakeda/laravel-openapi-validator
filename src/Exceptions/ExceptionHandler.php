<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Exceptions;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Exceptions\Handler;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler extends Handler
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
    ) {
    }

    public function renderWithStatusCode($request, \Throwable $e, int $status): Response
    {
        $current = $this->prepareException($e);
        if ($current instanceof HttpException) {
            $status = $current->getStatusCode();
        }

        $json = [
            'title' => class_basename($current),
            'detail' => $current->getMessage() ?: null,
            'status' => $status,
        ];

        while ($current) {
            $current = $current->getPrevious();

            if ($current instanceof SchemaMismatch) {
                $json['breadcrumb'] = $current->dataBreadCrumb()?->buildChain() ?: null;
                break;
            }
        }

        return $this->responseFactory->json($json, $status)->setStatusCode($status);
    }
}
