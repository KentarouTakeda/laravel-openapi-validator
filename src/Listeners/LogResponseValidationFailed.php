<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Listeners;

class LogResponseValidationFailed extends LogValidationFailed
{
    protected function getLogLevel(): ?string
    {
        return $this->config->getResErrorLogLevel();
    }
}
