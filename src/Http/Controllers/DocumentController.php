<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use Symfony\Component\HttpFoundation\Response;

class DocumentController
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    public function view(Request $request): View
    {
        $providerName = basename($request->getPathInfo());

        $schemaRepository = app()->makeWith(SchemaRepository::class, [
            'providerName' => $providerName,
        ]);
        assert($schemaRepository instanceof SchemaRepository);

        return view('openapi-validator::documents', [
            'json' => $schemaRepository->getJson(),
        ]);
    }

    public function asset(string $path): Response
    {
        $filePath = base_path("vendor/swagger-api/swagger-ui/dist/{$path}");
        $extension = $this->filesystem->extension($path);

        try {
            $this->filesystem->get($filePath);
        } catch (FileNotFoundException) {
            abort(404);
        }

        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'text/javascript',
        ];

        return response()->file($filePath, [
            'Content-Type' => $mimeTypes[$extension] ?? 'text/plain',
        ]);
    }
}
