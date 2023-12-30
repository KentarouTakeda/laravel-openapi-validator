<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\InvalidConfigException;
use KentarouTakeda\Laravel\OpenApiValidator\ResolverInterface;
use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class SchemaRepository
{
    private readonly string $json;

    public function __construct(
        // Input via the service container during validation execution
        string $providerName,
        // Input external libraries with dependency injection
        private readonly Config $config,
        private readonly ValidatorBuilder $validatorBuilder,
        private readonly Filesystem $filesystem,
    ) {
        if ($this->filesystem->exists($cacheFileName = $this->config->getCacheFileName($providerName))) {
            $this->json = $this->filesystem->get($cacheFileName);
            $this->validatorBuilder->fromJson($this->json);

            return;
        }

        $provider = $this->config->getProviderSettings($providerName);

        $resolverClass = $provider['resolver'];

        if (!class_exists($resolverClass)) {
            throw new InvalidConfigException('Unknown resolver class ');
        }

        if (!is_subclass_of($resolverClass, ResolverInterface::class)) {
            throw new InvalidConfigException('Resolver class must implement ResolverInterface');
        }

        $this->json = app()->make($resolverClass)->getJson($provider);

        $this->validatorBuilder->fromJson($this->json);
    }

    public function getJson(): string
    {
        return $this->json;
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
