# OpenAI Agents for Laravel - Fully Prepared by CLAUDE AI - Replacated from [openai-agents-python](https://github.com/openai/openai-agents-python)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/openai/agents.svg?style=flat-square)](https://packagist.org/packages/openai/agents)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/openai/openai-agents-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/openai/openai-agents-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Lint Action Status](https://img.shields.io/github/actions/workflow/status/openai/openai-agents-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/openai/openai-agents-laravel/actions?query=workflow%3Afix-php-code-style-issues+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/openai/agents.svg?style=flat-square)](https://packagist.org/packages/openai/agents)

The OpenAI Agents for Laravel package is a lightweight yet powerful framework for building multi-agent AI workflows in Laravel applications. This package is a Laravel port of the [OpenAI Agents Python SDK](https://github.com/openai/openai-agents-python).

## Features

- **Agent loop**: Built-in agent loop that handles calling tools, sending results to the LLM, and looping until the LLM is done
- **Laravel-first**: Uses built-in Laravel features to orchestrate and chain agents
- **Handoffs**: A powerful feature to coordinate and delegate between multiple agents
- **Guardrails**: Run input validations and checks in parallel to your agents, breaking early if the checks fail
- **Function tools**: Turn any PHP method into a tool, with automatic schema generation
- **Tracing**: Built-in tracing that lets you visualize, debug and monitor your workflows

## Installation

You can install the package via composer:

```bash
composer require openai/agents
```

Then publish the configuration file:

```bash
php artisan vendor:publish --tag="agents-config"
```

## Configuration

Set your OpenAI API key in your `.env` file:

```
OPENAI_API_KEY=your-api-key
OPENAI_DEFAULT_MODEL=gpt-4o
```

## Basic Usage

### Hello world example

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant");

$result = Runner::runSync($agent, "Write a haiku about recursion in programming.");
echo $result->getTextOutput();

// Code within the code,
// Functions calling themselves,
// Infinite loop's dance.
```

### Handoffs example

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$spanishAgent = Agent::create(
    "Spanish agent",
    "You only speak Spanish."
);

$englishAgent = Agent::create(
    "English agent",
    "You only speak English"
);

$triageAgent = Agent::create(
    "Triage agent",
    "Handoff to the appropriate agent based on the language of the request."
)->withHandoffs([$spanishAgent, $englishAgent]);

$result = Runner::runSync($triageAgent, "Hola, ¿cómo estás?");
echo $result->getTextOutput();
// ¡Hola! Estoy bien, gracias por preguntar. ¿Y tú, cómo estás?
```

### Functions example

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Tools\Tool;

$weatherTool = new Tool(
    'get_weather',
    'Get the weather for a city',
    function (RunContext $context, array $args) {
        $city = $args['city'] ?? 'Unknown';
        return "The weather in {$city} is sunny.";
    }
);

$agent = Agent::create(
    "Hello world",
    "You are a helpful agent."
)->withTools([$weatherTool]);

$result = Runner::runSync($agent, "What's the weather in Tokyo?");
echo $result->getTextOutput();
// The weather in Tokyo is sunny.
```

## Documentation

For detailed documentation, visit the [OpenAI Agents for Laravel documentation](https://github.com/your-username/openai-agents-laravel/blob/main/docs/index.md).

- [Quickstart](docs/quickstart.md)
- [Agents](docs/agents.md)
- [Running Agents](docs/running_agents.md)
- [Tools](docs/tools.md)
- [Handoffs](docs/handoffs.md)
- [Guardrails](docs/guardrails.md)
- [Models](docs/models.md)
- [Tracing](docs/tracing.md)
- [Examples](docs/examples.md)
- [API Reference](docs/ref/index.md)

## Examples

Check out the [examples directory](examples) for more usage examples:

- [Hello World](examples/hello_world.php): A simple agent example
- [Handoffs](examples/handoffs.php): An example of agent handoffs
- [Functions](examples/functions.php): An example using function tools
- [Laravel Controller Example](examples/AgentController.php): An example Laravel controller for synchronous and streaming agent responses

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please report security vulnerabilities to [security@example.com](mailto:security@example.com).

## Credits

- [Your Name](https://github.com/your-username)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.