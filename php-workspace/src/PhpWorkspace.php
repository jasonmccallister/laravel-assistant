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
    #[Doc("Show the output of the git changes.")]
    public function diff(): ?string
    {
        // make sure the container is a git repository
        return $this->container->withExec(["git", "diff"])->stdout();
    }

    #[DaggerFunction]
    #[Doc("Lists the contents of the application directory.")]
    public function listDirectory(): Directory
    {
        return $this->container->directory("/app");
    }

    #[DaggerFunction]
    #[Doc("Reads a file from the container.")]
    public function readFile(string $path): ?string
    {
        try {
            $file = $this->container->file($path)->contents();
        } catch (\Exception $e) {
            return null;
        }

        return $file;
    }

    #[DaggerFunction]
    #[Doc("Runs the tests in the container.")]
    public function runTests(): string
    {
        return $this->container->withExec(["php", "artisan", "test"])->stdout();
    }

    #[DaggerFunction]
    #[Doc("Writes a file to the container.")]
    public function writeFile(string $path, string $contents): File
    {
        return $this->container->withNewFile($path, $contents)->file($path);
    }
}
