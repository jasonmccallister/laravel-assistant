# Laravel Assistant

This is an example Dagger LLM Module, written in PHP, that can be used to look at an existing Laravel project, check git for any changes, and assist with what test are needed.

## Installation

Ensure you have the [latest version of Dagger](https://github.com/dagger/agents?tab=readme-ov-file#1-install-dagger) installed

1. Create a new Laravel project (`laravel new my-project`)
2. Add a supported environment variable to configure Dagger LLM in your `.env` file (I'm using `OPENAI_API_KEY` for this example)
3. Make a change to your Laravel project (e.g. add a new route and controller)
4. Run `dagger -m github.com/jasonmccallister/laravel-assistant call assist` in your Laravel project directory and view the output.
