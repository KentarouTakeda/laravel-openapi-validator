<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Composer\InstalledVersions;
use Vyuldashev\LaravelOpenApi\Generator;

function isSwaggerUIInstalled(): bool
{
    return InstalledVersions::isInstalled('swagger-api/swagger-ui');
}

function isLaravelOpenAPIInstalled(): bool
{
    // The original package has stalled and forks under other names are active, so detect by class
    return class_exists(Generator::class);
}

function isl5SwaggerInstalled(): bool
{
    return InstalledVersions::isInstalled('darkaonline/l5-swagger');
}
