<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use KentarouTakeda\Laravel\OpenApiValidator\Exceptions\LackOfDependenciesException;
use KentarouTakeda\Laravel\OpenApiValidator\ResolverInterface;
use L5Swagger\GeneratorFactory;

use function KentarouTakeda\Laravel\OpenApiValidator\isl5SwaggerInstalled;

class L5SwaggerResolver implements ResolverInterface
{
    private readonly GeneratorFactory $generatorFactory;

    public function __construct(
        private readonly Repository $repository,
        private readonly Filesystem $filesystem,
    ) {
        if (!isl5SwaggerInstalled()) {
            throw new LackOfDependenciesException('L5Swagger is not installed.', class: GeneratorFactory::class);
        }

        $this->generatorFactory = app()->make(GeneratorFactory::class);
    }

    public function getJson(array $options): string
    {
        $name = $options['documentation'];
        $path = $this->repository->get('l5-swagger.defaults.paths.docs');
        $file = $this->repository->get("l5-swagger.documentations.{$name}.paths.docs_json");

        $generator = $this->generatorFactory->make($name);
        $generator->generateDocs();

        return $this->filesystem->get($path.'/'.$file);
    }
}
