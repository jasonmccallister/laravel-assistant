<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;
use Dagger\File;

use function Dagger\dag;

#[DaggerObject]
class PhpWorkspace
{
    public Container $container;

    #[DaggerFunction]
    public function __construct(
        #[Doc("The source directory.")] private readonly Directory $source
    ) {
        $this->container = dag()
            ->php()
            ->setup()
            ->withWorkdir("/app")
            ->withMountedDirectory("/app", $this->source)
            ->withMountedDirectory(
                "/app/vendor",
                dag()->composer(source: $this->source)->install()
            )
            ->withDefaultTerminalCmd(["/bin/bash"]);
    }

    #[DaggerFunction]
    public function diff(): string
    {
        return $this->container->withExec(["git", "diff"])->stdout();
    }

    #[DaggerFunction]
    public function read(string $path): ?string
    {
        try {
            $file = $this->container->file($path)->contents();
        } catch (\Exception $e) {
            return null;
        }

        return $file;
    }

    #[DaggerFunction]
    public function test(): string
    {
        return $this->container->withExec(["php", "artisan", "test"])->stdout();
    }

    #[DaggerFunction]
    #[Doc("Writes a file to the container.")]
    public function write(string $path, string $contents): File
    {
        return $this->container->withNewFile($path, $contents)->file($path);
    }
}
