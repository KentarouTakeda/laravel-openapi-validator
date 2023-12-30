<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;

class ClearCommand extends Command
{
    protected $signature = 'openapi-validator:clear';

    protected $description = 'Remove the validator cache';

    public function handle(
        Config $config,
        Filesystem $file
    ): int {
        foreach ($config->getProviderNames() as $providerName) {
            $cacheFileName = $config->getCacheFileName($providerName);

            $file->delete($cacheFileName);
        }

        $this->components->info('OpenAPI validator removed successfully.');

        return Command::SUCCESS;
    }
}
