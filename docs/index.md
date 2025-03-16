# OpenAI Agents for Laravel

The OpenAI Agents for Laravel package enables you to build agentic AI applications within your Laravel applications. It provides a lightweight, easy-to-use framework with very few abstractions. The Agents package has a small set of core primitives:

- **Agents**: LLMs equipped with instructions and tools
- **Handoffs**: Allow agents to delegate to other agents for specific tasks
- **Guardrails**: Enable the inputs and outputs to agents to be validated
- **Tools**: Function-based tools that agents can use to interact with your application

In combination with Laravel, these primitives are powerful enough to express complex relationships between tools and agents, allowing you to build real-world applications without a steep learning curve. The package also comes with built-in **tracing** that lets you visualize and debug your agentic flows.

## Why use OpenAI Agents for Laravel

This Laravel package provides:

1. A simple, Laravel-friendly interface for creating and running AI agents
2. Built-in integration with the OpenAI API
3. First-class Laravel patterns, using Services, Facades, and dependency injection
4. A ready-to-use toolkit for implementing AI agents in your web applications

Here are the main features of the package:

- **Agent loop**: Built-in agent loop that handles calling tools, sending results to the LLM, and looping until the LLM is done
- **Laravel-first**: Uses built-in Laravel features to orchestrate and chain agents, rather than requiring you to learn new abstractions
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

Set your OpenAI API key in your `.env` file:

```
OPENAI_API_KEY=your-api-key
OPENAI_DEFAULT_MODEL=gpt-4o
```

## Hello world example

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

## Next steps

- Learn about [Agents](agents.md)
- Learn about [Running Agents](running_agents.md)
- Learn about [Tools](tools.md), [Guardrails](guardrails.md), and [Models](models.md)
- Check out [Examples](examples.md)