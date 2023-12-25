<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

interface ResolverInterface
{
    public function supports(): string;

    /**
     * @param array{collection: string} $options
     */
    public function setOptions(array $options): static;

    public function getJson(): string;
}
