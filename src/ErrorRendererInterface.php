<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ErrorRendererInterface
{
    /**
     * Render the exception that occurred during processing as a response
     *
     * @param int $status Specify the response code. This *MAY* be overwritten depending on the type of exception.
     */
    public function render(Request $request, \Throwable $error, int $status): Response;
}
