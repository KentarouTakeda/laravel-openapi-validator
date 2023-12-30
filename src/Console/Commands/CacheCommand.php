<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;

class CacheCommand extends Command
{
    protected $signature = <<<EOD
        openapi-validator:cache
        {provider? : Name of the provider that creates the cache}
        {--all : Create cache for all providers}
    EOD;

    protected $description = 'Create a validator cache file for faster validation';

    public function handle(
        Config $config,
        Filesystem $file
    ): int {
        $file->makeDirectory($config->getCacheDirectory(), 0777, true, true);

        $providerNames = $this->option('all') ?
            $config->getProviderNames() :
            [(string) $this->argument('provider') ?: $config->getDefaultProviderName()];

        foreach ($providerNames as $providerName) {
            $repository = app()->makeWith(
                SchemaRepository::class,
                ['providerName' => $providerName]
            );
            assert($repository instanceof SchemaRepository);

            $file->put(
                $config->getCacheFileName($providerName),
                $repository->getJson(),
            );
            $this->components->task("Make cache for {$providerName}");
        }

        $this->components->info('OpenAPI validator cached successfully.');

        return Command::SUCCESS;
    }
}
