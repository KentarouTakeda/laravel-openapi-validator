<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class SchemaRepository
{
    private ValidatorBuilder $validatorBuilder;

    public function __construct(string $provider)
    {
        $this->validatorBuilder = new ValidatorBuilder();
    }

    public function getRequestValidator(): RequestValidator
    {
        return $this->validatorBuilder->getRequestValidator();
    }

    public function getResponseValidator(): ResponseValidator
    {
        return $this->validatorBuilder->getResponseValidator();
    }
}
