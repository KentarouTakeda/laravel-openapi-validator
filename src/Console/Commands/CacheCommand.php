<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console\Commands;

use Illuminate\Console\Command;

class CacheCommand extends Command
{
    protected $signature = 'openapi-validator:cache';

    protected $description = 'Create a validator cache file for faster validation';

    public function handle(): int
    {
        return Command::SUCCESS;
    }
}
