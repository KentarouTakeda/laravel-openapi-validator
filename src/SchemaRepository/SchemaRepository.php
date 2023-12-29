<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\InvalidConfigException;
use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class SchemaRepository
{
    public function __construct(
        // Input via the service container during validation execution
        string $providerName,
        // Input external libraries with dependency injection
        private readonly ValidatorBuilder $validatorBuilder,
    ) {
        $provider = config()->get("openapi-validator.providers.{$providerName}");
        if (!$provider) {
            throw new \InvalidArgumentException("laravel-openapi-validator: Provider {$providerName} is not defined");
        }

        $class = $provider['resolver'];

        if (!class_exists($class)) {
            throw new InvalidConfigException('Unknown resolver class ');
        }

        if (!is_subclass_of($class, ResolverInterface::class)) {
            throw new InvalidConfigException('Resolver class must implement ResolverInterface');
        }

        $resolver = app()->make($class);

        $this->validatorBuilder->fromJson(
            $resolver->getJson($provider)
        );
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
