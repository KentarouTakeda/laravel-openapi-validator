<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Exceptions;

class LackOfDependenciesException extends \LogicException
{
    public function __construct(
        string $message,
        public readonly string $class,
    ) {
        parent::__construct($message);
    }
}
