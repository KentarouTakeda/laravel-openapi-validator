<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Config;

use Illuminate\Contracts\Config\Repository;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\InvalidConfigException;
use Psr\Log\LogLevel;

class Config
{
    public function __construct(
        private readonly Repository $repository
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function getProviderNames(): array
    {
        $providers = $this->repository->get('openapi-validator.providers');

        if (!is_array($providers)) {
            throw new InvalidConfigException(message: 'openapi-validator.providers must be an array');
        }

        return array_keys($providers);
    }

    public function getDefaultProviderName(): string
    {
        $defaultProviderName = $this->repository->get('openapi-validator.default');

        if (!is_string($defaultProviderName)) {
            throw new InvalidConfigException(message: 'openapi-validator.default_provider must be a string');
        }

        return $defaultProviderName;
    }

    /**
     * @return array<string, string>
     */
    public function getProviderSettings(string $providerName): array
    {
        $provider = $this->repository->get("openapi-validator.providers.{$providerName}");

        if (!$provider) {
            throw new InvalidConfigException(message: "Provider {$providerName} is not defined");
        }

        if (!is_array($provider)) {
            throw new InvalidConfigException(message: "Provider {$providerName} must be an array");
        }

        return $provider;
    }

    public function getCacheDirectory(): string
    {
        $cacheDirectory = $this->repository->get('openapi-validator.cache_directory');

        if (!is_string($cacheDirectory)) {
            throw new InvalidConfigException(message: 'openapi-validator.cache_directory must be a string');
        }

        return $cacheDirectory;
    }

    public function getCacheFileName(string $providerName): string
    {
        return str($this->getCacheDirectory())
            ->finish(DIRECTORY_SEPARATOR)
            ->append($providerName)
            ->append('.json')
            ->toString();
    }

    public function getErrorOnNoPath(): bool
    {
        return (bool) $this->repository->get('openapi-validator.error_on_no_path');
    }

    public function getIncludeReqErrorDetailInResponse(): bool
    {
        return (bool) $this->repository->get('openapi-validator.include_req_error_detail_in_response');
    }

    public function getIncludeResErrorDetailInResponse(): bool
    {
        return (bool) $this->repository->get('openapi-validator.include_res_error_detail_in_response');
    }

    public function getIncludeTraceInResponse(): bool
    {
        return (bool) $this->repository->get('openapi-validator.include_trace_in_response');
    }

    /**
     * @return array<int, int>
     */
    public function getNonValidatedResponseCodes(): array
    {
        $codes = $this->repository->get('openapi-validator.non_validated_response_codes');

        if (!is_array($codes)) {
            throw new InvalidConfigException(message: 'openapi-validator.non_validated_response_codes must be an array');
        }

        return $codes;
    }

    public function getReqErrorLogLevel(): string
    {
        return $this->getLogLevelString($this->repository->get('openapi-validator.req_error_log_level'));
    }

    public function getResErrorLogLevel(): string
    {
        return $this->getLogLevelString($this->repository->get('openapi-validator.res_error_log_level'));
    }

    public function getRespondErrorOnResValidationFailure(): bool
    {
        return (bool) $this->repository->get('openapi-validator.respond_error_on_res_validation_failure');
    }

    private function getLogLevelString(mixed $logLevel): string
    {
        if (!in_array($logLevel, [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ], true)) {
            throw new InvalidConfigException(message: 'openapi-validator.log_level must be a valid LogLevel');
        }

        return $logLevel;
    }
}
