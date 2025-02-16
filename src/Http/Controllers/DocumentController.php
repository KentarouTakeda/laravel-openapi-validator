<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository\SchemaRepository;
use Symfony\Component\HttpFoundation\Response;

class DocumentController
{
    public function __construct(
        private readonly Config $config,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function redirect(): RedirectResponse
    {
        $providerName = $this->config->getDefaultProviderName();

        return redirect()->route("openapi-validator.document.{$providerName}");
    }

    public function view(Request $request): View
    {
        $providerName = basename($request->getPathInfo());

        $schemaRepository = app()->makeWith(SchemaRepository::class, [
            'providerName' => $providerName,
        ]);
        assert($schemaRepository instanceof SchemaRepository);

        // @phpstan-ignore argument.type
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
            'css' => 'text/css; charset=UTF-8',
            'js' => 'text/javascript; charset=UTF-8',
        ];

        return response()->file($filePath, [
            'Content-Type' => $mimeTypes[$extension] ?? 'text/plain',
        ]);
    }
}
