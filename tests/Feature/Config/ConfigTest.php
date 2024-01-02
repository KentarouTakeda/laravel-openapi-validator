<?php

declare(strict_types=1);

namespace KentarouTakeda\SafeRouting\Tests\Feature\Config;

use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\InvalidConfigException;
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

    /**
     * @test
     */
    public function getProviderSettingsReturnsProviderSettings(): void
    {
        $providerNames = $this->config->getProviderNames();

        foreach ($providerNames as $providerName) {
            $providerSettings = $this->config->getProviderSettings($providerName);

            $this->assertIsArray($providerSettings);
        }
    }

    /**
     * @test
     */
    public function getProviderSettingsThrowsInvalidConfigExceptionWhenProviderIsNotDefined(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Provider foo is not defined');

        $this->config->getProviderSettings('foo');
    }

    /**
     * @test
     */
    public function getCacheDirectoryReturnsCacheDirectory(): void
    {
        $cacheDirectory = $this->config->getCacheDirectory();

        $this->assertIsString($cacheDirectory);
    }

    /**
     * @test
     */
    public function getCacheFileNameReturnsCacheFileName(): void
    {
        $cacheFileName = $this->config->getCacheFileName('foo');

        $this->assertStringEndsWith(DIRECTORY_SEPARATOR.'foo.json', $cacheFileName);
    }

    /**
     * @test
     */
    public function getErrorOnNoPathReturnsBool(): void
    {
        $errorOnNoPath = $this->config->getErrorOnNoPath();

        $this->assertIsBool($errorOnNoPath);
    }

    /**
     * @test
     */
    public function getIncludeReqErrorDetailInResponseReturnsBool(): void
    {
        $includeReqErrorInResponse = $this->config->getIncludeReqErrorDetailInResponse();

        $this->assertIsBool($includeReqErrorInResponse);
    }

    /**
     * @test
     */
    public function getIncludeResErrorDetailInResponseReturnsBool(): void
    {
        $includeResErrorInResponse = $this->config->getIncludeResErrorDetailInResponse();

        $this->assertIsBool($includeResErrorInResponse);
    }

    /**
     * @test
     */
    public function getIncludeOriginalResInResponseReturnsBool(): void
    {
        $includeOriginalResInResponse = $this->config->getIncludeOriginalResInResponse();

        $this->assertIsBool($includeOriginalResInResponse);
    }

    /**
     * @test
     */
    public function getIncludeTraceInResponseReturnsBool(): void
    {
        $includeTraceInResponse = $this->config->getIncludeTraceInResponse();

        $this->assertIsBool($includeTraceInResponse);
    }

    /**
     * @test
     */
    public function getReqErrorLogLevelReturnsLogLevel(): void
    {
        $requestErrorLogLevel = $this->config->getReqErrorLogLevel();

        $this->assertIsString($requestErrorLogLevel);
    }

    /**
     * @test
     */
    public function getResErrorLogLevelReturnsLogLevel(): void
    {
        $requestErrorLogLevel = $this->config->getResErrorLogLevel();

        $this->assertIsString($requestErrorLogLevel);
    }
}
