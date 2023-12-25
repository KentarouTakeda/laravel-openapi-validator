<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use Vyuldashev\LaravelOpenApi\Generator;

class LaravelOpenApiResolver implements ResolverInterface
{
    private string $collection;

    public function __construct(
        private readonly Generator $generator,
    ) {
    }

    public function supports(): string
    {
        return 'laravel-openapi';
    }

    public function setOptions(array $options): static
    {
        $this->collection = $options['collection'];

        return $this;
    }

    public function getJson(): string
    {
        return $this->generator
            ->generate($this->collection)
            ->toJson(JSON_UNESCAPED_UNICODE);
    }
}
