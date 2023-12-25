<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Exceptions;

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use Psr\Http\Message\RequestInterface;

class PathNotFoundException extends \LogicException
{
    public function __construct(
        public readonly RequestInterface $request,
        NoPath $previous,
    ) {
        parent::__construct(
            message: "Path not found: {$request->getMethod()} {$previous->path()}",
            previous: $previous
        );
    }
}
