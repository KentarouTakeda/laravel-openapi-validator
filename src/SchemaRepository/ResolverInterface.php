<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

interface ResolverInterface
{
    public function supports(): string;

    /**
     * @param array<string,string> $options
     */
    public function getJson(array $options): string;
}
