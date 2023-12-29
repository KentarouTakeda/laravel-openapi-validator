<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use Vyuldashev\LaravelOpenApi\Generator;

class LaravelOpenApiResolver implements ResolverInterface
{
    public function __construct(
        private readonly Generator $generator,
    ) {
    }

    public function supports(): string
    {
        return 'laravel-openapi';
    }

    public function getJson(array $options): string
    {
        return $this->generator
            ->generate($options['collection'])
            ->toJson(JSON_UNESCAPED_UNICODE);
    }
}
