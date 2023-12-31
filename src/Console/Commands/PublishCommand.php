<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console\Commands;

use Illuminate\Console\Command;
use KentarouTakeda\Laravel\OpenApiValidator\ServiceProvider;

class PublishCommand extends Command
{
    protected $signature = <<<EOD
        openapi-validator:publish
        {--force : Overwrite any existing files}
    EOD;

    protected $description = 'Publish config file for OpenAPI validator';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--provider' => ServiceProvider::class,
            '--force' => (bool) $this->option('force'),
        ]);

        return Command::SUCCESS;
    }
}
