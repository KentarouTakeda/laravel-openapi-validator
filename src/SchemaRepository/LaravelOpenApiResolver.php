<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\LackOfDependenciesException;
use KentarouTakeda\Laravel\OpenApiValidator\ResolverInterface;
use Vyuldashev\LaravelOpenApi\Generator;

class LaravelOpenApiResolver implements ResolverInterface
{
    private readonly Generator $generator;

    public function __construct(
    ) {
        if (!class_exists(Generator::class)) {
            throw new LackOfDependenciesException(message: 'Laravel OpenAPI is not installed.', class: Generator::class);
        }

        $this->generator = app()->make(Generator::class);
    }

    public function getJson(array $options): string
    {
        return $this->generator
            ->generate($options['collection'])
            ->toJson(JSON_UNESCAPED_UNICODE);
    }
}
