<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ErrorRendererInterface
{
    /**
     * Render the exception that occurred during processing as a response
     */
    public function render(
        \Throwable $error,
        Request $request,
        Response $response = null,
    ): Response;
}
