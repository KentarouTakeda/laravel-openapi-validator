<?php

declare(strict_types=1);

namespace KentarouTakeda\Laravel\OpenApiValidator\SchemaRepository;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use L5Swagger\GeneratorFactory;

class L5SwaggerResolver implements ResolverInterface
{
    public function __construct(
        private readonly GeneratorFactory $generatorFactory,
        private readonly Repository $repository,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function supports(): string
    {
        return 'l5-swagger';
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
