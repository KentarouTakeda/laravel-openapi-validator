<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator;

use Composer\InstalledVersions;

function isSwaggerUIInstalled(): bool
{
    return InstalledVersions::isInstalled('swagger-api/swagger-ui');
}

function isLaravelOpenAPIInstalled(): bool
{
    return InstalledVersions::isInstalled('vyuldashev/laravel-openapi');
}

function isl5SwaggerInstalled(): bool
{
    return InstalledVersions::isInstalled('darkaonline/l5-swagger');
}
