<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;

class ClearCommand extends Command
{
    protected $signature = <<<EOD
        openapi-validator:clear
        {provider? : Name of the provider that creates the cache}
        {--all : Create cache for all providers}
    EOD;

    protected $description = 'Remove the validator cache';

    public function handle(
        Config $config,
        Filesystem $file
    ): int {
        $providerNames = $this->option('all') ?
            $config->getProviderNames() :
            [(string) $this->argument('provider') ?: $config->getDefaultProviderName()];

        foreach ($providerNames as $providerName) {
            $cacheFileName = $config->getCacheFileName($providerName);

            $file->delete($cacheFileName);

            $this->components->task("Delete cache for {$providerName}");
        }

        $this->components->info('OpenAPI validator removed successfully.');

        return Command::SUCCESS;
    }
}
