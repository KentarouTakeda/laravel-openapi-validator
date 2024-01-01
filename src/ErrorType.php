<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

enum ErrorType
{
    /**
     * Request validation error
     */
    case Request;

    /**
     * Response validation error
     */
    case Response;
}
