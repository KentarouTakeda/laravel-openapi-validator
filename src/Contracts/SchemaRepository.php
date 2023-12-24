<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Contracts;

use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;

interface SchemaRepository
{
    public function getRequestValidator(): RequestValidator;

    public function getResponseValidator(): ResponseValidator;
}
