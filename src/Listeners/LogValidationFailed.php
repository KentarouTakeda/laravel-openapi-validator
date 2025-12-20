<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Listeners;

use Illuminate\Log\LogManager;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Events\ValidationFailedInterface;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

abstract class LogValidationFailed
{
    abstract protected function getLogLevel(): ?string;

    public function __construct(
        private readonly LogManager $logManager,
        protected readonly Config $config,
    ) {
    }

    public function handle(ValidationFailedInterface $event): void
    {
        if (!$this->getLogLevel()) {
            return;
        }

        $schemaMismatch = $this->findSchemaMismatch($event->getThrowable());

        $this->logManager->log(
            $this->getLogLevel(),
            class_basename($event).': '.$event->getThrowable()->getMessage(),
            [
                ...(
                    $schemaMismatch ? [
                        'detail' => $schemaMismatch->getMessage(),
                        'pointer' => $schemaMismatch->dataBreadCrumb()?->buildChain(),
                    ] : []
                ),
                'error' => $event->getThrowable(),
                'request' => $event->getRequest(),
                'response' => $event->getResponse(),
            ],
        );
    }

    private function findSchemaMismatch(\Throwable $error): ?SchemaMismatch
    {
        while ($error) {
            $error = $error->getPrevious();

            if ($error instanceof SchemaMismatch) {
                return $error;
            }
        }

        return null;
    }
}
