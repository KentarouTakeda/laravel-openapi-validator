<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Events;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ValidationFailedInterface
{
    public function getThrowable(): \Throwable;

    public function getRequest(): Request;

    public function getResponse(): ?Response;
}
