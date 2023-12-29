<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Console;

use Illuminate\Console\Command;

class ClearCommand extends Command
{
    protected $signature = 'openapi-validator:clear';

    protected $description = 'Remove the validator cache';

    public function handle(): int
    {
        return Command::SUCCESS;
    }
}
