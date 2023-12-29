<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class SchemaRepository
{
    public function __construct(
        string $providerName,
        private readonly ValidatorBuilder $validatorBuilder,
        LaravelOpenApiResolver $laravelOpenApiResolver,
    ) {
        $provider = config()->get("openapi-validator.providers.{$providerName}");
        if (!$provider) {
            throw new \InvalidArgumentException("laravel-openapi-validator: Provider {$providerName} is not defined");
        }

        foreach (func_get_args() as $arg) {
            if (!($arg instanceof ResolverInterface)) {
                continue;
            }
            if ($arg->supports() !== $provider['driver']) {
                continue;
            }

            $this->validatorBuilder->fromJson(
                $arg->getJson($provider)
            );

            return;
        }

        throw new \InvalidArgumentException("laravel-openapi-validator: Provider {$providerName} is not supported");
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
