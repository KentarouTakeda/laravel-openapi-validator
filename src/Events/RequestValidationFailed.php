<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Events;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestValidationFailed implements ValidationFailedInterface
{
    public function __construct(
        private readonly \Throwable $throwable,
        private readonly Request $request,
    ) {
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): ?Response
    {
        return null;
    }
}
