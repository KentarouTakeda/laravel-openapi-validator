<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

interface ResolverInterface
{
    /**
     * @param array<string,string> $options
     */
    public function getJson(array $options): string;
}
