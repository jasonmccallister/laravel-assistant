<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;

use function Dagger\dag;

#[DaggerObject]
#[Doc("A LLM module to assist with writing Laravel tests.")]
class LaravelAssistant
{
    #[DaggerFunction]
    public function __construct(
        #[Doc("The source directory.")] private readonly Directory $source
    ) {}

    #[DaggerFunction]
    #[Doc("Assists with writing tests.")]
    public function writeTests(): Container
    {
        $before = dag()->phpWorkspace($this->source);

        $after = dag()
            ->llm()
            ->withPhpWorkspace($before)
            ->withPrompt(
                <<<EOT
You are an expert in Laravel. You understand the framework and its ecosystem. You have a deep understanding of the Laravel lifecycle and can build complex applications with ease. You are comfortable with the command line and can navigate the Laravel directory structure with ease.

Examine the source code and write tests that are missing and ensure all tests pass.

Use the write tool to put the complete test file. Before using the write tool, use the test tool to ensure tests are passing.

Don't stop until your tests pass.
EOT
            );

        return $after->container();
    }
}
