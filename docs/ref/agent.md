# Agent

The `Agent` class is the core component of the OpenAI Agents for Laravel package. It represents an AI agent with specific instructions, tools, and other configuration settings.

## Basic Usage

```php
use OpenAI\Agents\Agent;

$agent = new Agent(
    "Customer Support",
    "You are a helpful customer support agent for a clothing retailer. Help customers with inquiries about products, orders, and returns."
);
```

Or using the Facade:

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create(
    "Customer Support",
    "You are a helpful customer support agent for a clothing retailer. Help customers with inquiries about products, orders, and returns."
);
```

## Constructor

### `__construct(string $name, $instructions = null, ?ModelSettings $modelSettings = null)`

Creates a new agent instance.

#### Parameters

- `$name` (string): The name of the agent.
- `$instructions` (string|Closure|null): The instructions for the agent (system prompt). Can be a string or a Closure that returns a string.
- `$modelSettings` (ModelSettings|null): Optional model settings for the agent.

## Methods

### Configuration Methods

These methods allow you to configure the agent. They all return `$this` for method chaining.

#### `withHandoffDescription(string $description): self`

Sets the description that will be used when this agent is offered as a handoff option.

```php
$agent->withHandoffDescription("Specialist for technical support inquiries");
```

#### `withHandoffs(array $handoffs): self`

Sets the handoffs for this agent.

```php
$agent->withHandoffs([$technicalAgent, $billingAgent]);
```

#### `withModel($model): self`

Sets the model for this agent.

```php
$agent->withModel("gpt-4o");
// or
$agent->withModel($customModel);
```

#### `withModelSettings(ModelSettings $settings): self`

Sets the model settings for this agent.

```php
$agent->withModelSettings(new ModelSettings(0.7, 1.0));
```

#### `withTools(array $tools): self`

Sets the tools for this agent.

```php
$agent->withTools([$weatherTool, $calculatorTool]);
```

#### `withInputGuardrails(array $guardrails): self`

Sets the input guardrails for this agent.

```php
$agent->withInputGuardrails([$contentFilterGuardrail]);
```

#### `withOutputGuardrails(array $guardrails): self`

Sets the output guardrails for this agent.

```php
$agent->withOutputGuardrails([$factualityGuardrail]);
```

#### `withOutputType(string $type): self`

Sets the output type for this agent.

```php
$agent->withOutputType("json");
```

### Utility Methods

#### `clone(array $attributes = []): self`

Creates a copy of the agent with the given attributes changed.

```php
$specializedAgent = $baseAgent->clone([
    'name' => 'Specialized Agent',
    'instructions' => 'You are a specialized assistant for technical topics.',
]);
```

#### `asTool(?string $toolName = null, ?string $toolDescription = null, ?callable $customOutputExtractor = null): Tool`

Transforms this agent into a tool, callable by other agents.

```php
$calculatorTool = $calculatorAgent->asTool(
    "calculate",
    "Solve complex mathematical problems"
);
```

#### `getSystemPrompt(RunContext $context): ?string`

Gets the system prompt for the agent.

```php
$systemPrompt = $agent->getSystemPrompt($context);
```

### Getter Methods

These methods allow you to retrieve the agent's configuration.

#### `getName(): string`

Gets the name of the agent.

```php
$name = $agent->getName();
```

#### `getHandoffDescription(): ?string`

Gets the handoff description.

```php
$description = $agent->getHandoffDescription();
```

#### `getHandoffs(): array`

Gets the handoffs.

```php
$handoffs = $agent->getHandoffs();
```

#### `getModel()`

Gets the model.

```php
$model = $agent->getModel();
```

#### `getModelSettings(): ModelSettings`

Gets the model settings.

```php
$settings = $agent->getModelSettings();
```

#### `getTools(): array`

Gets the tools.

```php
$tools = $agent->getTools();
```

#### `getInputGuardrails(): array`

Gets the input guardrails.

```php
$inputGuardrails = $agent->getInputGuardrails();
```

#### `getOutputGuardrails(): array`

Gets the output guardrails.

```php
$outputGuardrails = $agent->getOutputGuardrails();
```

#### `getOutputType(): ?string`

Gets the output type.

```php
$outputType = $agent->getOutputType();
```