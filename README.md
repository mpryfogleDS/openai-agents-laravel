# OpenAI Agents for Laravel

The OpenAI Agents SDK for Laravel is a lightweight yet powerful framework for building multi-agent workflows. This package is a Laravel port of the [OpenAI Agents Python SDK](https://github.com/openai/openai-agents-python).

## Core concepts:

1. **Agents**: LLMs configured with instructions, tools, guardrails, and handoffs
2. **Handoffs**: Allow agents to transfer control to other agents for specific tasks
3. **Guardrails**: Configurable safety checks for input and output validation
4. **Tracing**: Built-in tracking of agent runs, allowing you to view, debug and optimize your workflows

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

function getWeather(mixed $context, array $args): string {
    $city = $args['city'] ?? 'Unknown';
    return "The weather in {$city} is sunny.";
}

$agent = Agent::create(
    "Hello world",
    "You are a helpful agent."
)->withTools([function_tool('getWeather', 'Get the weather for a city')]);

$result = Runner::runSync($agent, "What's the weather in Tokyo?");
echo $result->getTextOutput();
// The weather in Tokyo is sunny.
```

## The agent loop

When you call `Runner::run()`, we run a loop until we get a final output.

1. We call the LLM, using the model and settings on the agent, and the message history.
2. The LLM returns a response, which may include tool calls.
3. If the response has a final output (see below for more on this), we return it and end the loop.
4. If the response has a handoff, we set the agent to the new agent and go back to step 1.
5. We process the tool calls (if any) and append the tool responses messages. Then we go to step 1.

There is a `maxTurns` parameter that you can use to limit the number of times the loop executes.

## License

This package is open-sourced software licensed under the MIT license.