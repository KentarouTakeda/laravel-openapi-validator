<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Tests\Feature;

use Illuminate\Filesystem\Filesystem;

trait TestWithTemporaryFilesTrait
{
    private function getTemporaryDirectory(): string
    {
        return storage_path('framework/testing');
    }

    private function clearTemporaryDirectory(): void
    {
        $filesystem = app()->make(Filesystem::class);

        foreach ($filesystem->allFiles($this->getTemporaryDirectory()) as $file) {
            $filesystem->delete($file->getPathname());
        }
    }
}
