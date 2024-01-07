<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Listeners;

use Illuminate\Log\LogManager;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Events\ValidationFailedInterface;

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

        $this->logManager->log(
            $this->getLogLevel(),
            class_basename($event).': '.$event->getThrowable()->getMessage(),
            [
                'error' => $event->getThrowable(),
                'request' => $event->getRequest(),
                'response' => $event->getResponse(),
            ],
        );
    }
}
