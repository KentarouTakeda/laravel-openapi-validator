<?php

declare(strict_types=1);

namespace KentarouTakeda\SafeRouting\Tests\Feature\Config;

use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature\TestCase;

class ConfigTest extends TestCase
{
    private Config $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = app()->make(Config::class);
    }

    /**
     * @test
     */
    public function getProviderNamesReturnsAllProviderNames(): void
    {
        $providerNames = $this->config->getProviderNames();

        $this->assertSame(
            ['laravel-openapi', 'l5-swagger'],
            $providerNames
        );
    }

    /**
     * @test
     */
    public function getDefaultProviderNameReturnsDefaultProviderName(): void
    {
        $defaultProviderName = $this->config->getDefaultProviderName();

        $this->assertSame(
            'laravel-openapi',
            $defaultProviderName
        );
    }
}
