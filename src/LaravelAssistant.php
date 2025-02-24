<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\DefaultPath;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;

use Dagger\File;
use function Dagger\dag;

#[DaggerObject]
#[Doc("A LLM module to assist with writing Laravel tests.")]
class LaravelAssistant
{
    private Container $container;

    #[DaggerFunction]
    public function __construct(
        #[DefaultPath("..")] #[
            Doc("The source directory.")
        ]
        private readonly Directory $source
    ) {
        $this->container = dag()
            ->php()
            ->setup()
            ->withWorkdir("/app")
            ->withMountedDirectory("/app", $this->source)
            ->withMountedDirectory(
                "/app/vendor",
                dag()->composer(source: $this->source)->install()
            );
    }

    #[DaggerFunction]
    #[Doc("Assists with writing tests.")]
    public function assist(): Container
    {
        $after = dag()
            ->llm()
            ->withContainer($this->container)
            ->withPromptVar("diff", $this->diff())
            ->withPromptFile(dag()->currentModule()->workdirFile("prompt.txt"));

        return $after->Container();
    }

    #[DaggerFunction]
    #[Doc("Returns the diff of the source directory.")]
    public function diff(): string
    {
        return $this->container->withExec(["git", "diff"])->stdout();
    }

    #[DaggerFunction]
    #[Doc("Runs the tests.")]
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
