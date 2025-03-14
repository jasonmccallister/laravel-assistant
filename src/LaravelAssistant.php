<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;

use Dagger\Error;
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
        $workspace = dag()->phpWorkspace($this->source);

        $diff = $workspace->diff();
        if ($diff === null) {
            throw new Error("No diff found.");
        }

        $after = dag()
            ->llm()
            ->withPromptVar("diff", $diff)
            ->withPhpWorkspace($workspace)
            ->withPrompt(
                <<<EOT
You are an expert in Laravel and PHP.
You have been given access to a php workspace and a code repository.

- Use the provided diff to examine what changes have been made to the project.
- Use the runTests tool to ensure all tests pass.
- Use the readFile tool to examine the source code in /app and write tests that are missing.
- Use the listDirectory tool to view the contents of the application.

<diff>
\$diff
</diff>
DON'T STOP UNTIL ALL TESTS PASS!
EOT
            );

        return $after->container();
    }
}
