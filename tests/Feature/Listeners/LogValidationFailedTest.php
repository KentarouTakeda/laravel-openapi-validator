<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\Listeners;

use Illuminate\Log\LogManager;
use KentarouTakeda\Laravel\OpenApiValidator\Events\ValidationFailedInterface;
use KentarouTakeda\Laravel\OpenApiValidator\Listeners\LogValidationFailed;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogValidationFailedTest extends TestCase
{
    #[Test]
    public function logsLevelAndMessageWithPointerAndDetail(): void
    {
        $this->mock(
            LogManager::class,
            fn (MockInterface $mock) => tap($mock, fn ($mock) => $mock->shouldReceive('log')->andReturnUsing(function ($level, $message, $context) {
                $this->assertSame('warning', $level);

                $this->assertSame(['foo', 'bar'], $context['pointer']);

                $this->assertSame('hoge', $context['detail']);

                $this->assertStringContainsString(': fuga', $message);
            })->once())
        );

        $logValidationFailed = app()->make(LogTestValidationFailed::class);

        $validationFailed = $this->mock(ValidationFailedInterface::class, function (MockInterface $mock) {
            $breadCrumb = (new BreadCrumb())
                ->addCrumb('foo')
                ->addCrumb('bar');

            $schemaMismatch = (new SchemaMismatch('hoge'))
                ->withBreadCrumb($breadCrumb);

            $mock->shouldReceive('getThrowable')->andReturn(
                new ValidationFailed('fuga', 0, $schemaMismatch),
            );

            $mock->shouldReceive('getRequest')->andReturn(new Request());

            $mock->shouldReceive('getResponse')->andReturn(new Response());
        });

        assert($validationFailed instanceof ValidationFailedInterface);

        $logValidationFailed->handle($validationFailed);
    }
}

class LogTestValidationFailed extends LogValidationFailed
{
    protected function getLogLevel(): ?string
    {
        return 'warning';
    }
}
