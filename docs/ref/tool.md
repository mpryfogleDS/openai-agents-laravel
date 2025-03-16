# Tool

The `Tool` class represents a function that an agent can call. Tools allow agents to interact with external systems, perform calculations, access databases, and more.

## Basic Usage

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

$weatherTool = new Tool(
    'get_weather',
    'Get the current weather for a location',
    function (RunContext $context, array $args) {
        $location = $args['location'] ?? 'Unknown';
        
        // In a real implementation, you would call a weather API
        return "The weather in $location is sunny and 75°F.";
    }
);
```

## Constructor

### `__construct(string $name, string $description, Closure $callback, ?array $parameters = null)`

Creates a new tool instance.

#### Parameters

- `$name` (string): The name of the tool.
- `$description` (string): The description of the tool.
- `$callback` (Closure): The function to call when the tool is invoked.
- `$parameters` (array|null): Optional JSON Schema parameters for the tool. If not provided, parameters will be inferred from the callback function.

## Static Methods

### `fromFunction(callable $callback, ?string $name = null, ?string $description = null): Tool`

Creates a new tool from a function.

#### Parameters

- `$callback` (callable): The function to convert into a tool.
- `$name` (string|null): Optional name for the tool. If not provided, the function name will be used.
- `$description` (string|null): Optional description for the tool. If not provided, the function's docblock will be used.

#### Returns

- `Tool`: A new tool instance.

```php
function getWeather(RunContext $context, array $args): string
{
    $location = $args['location'] ?? 'Unknown';
    return "The weather in $location is sunny and 75°F.";
}

$weatherTool = Tool::fromFunction('getWeather', null, 'Get the current weather for a location');
```

## Methods

### `execute(RunContext $context, array $arguments): mixed`

Executes the tool with the given arguments.

#### Parameters

- `$context` (RunContext): The context for the tool execution.
- `$arguments` (array): The arguments to pass to the tool function.

#### Returns

- `mixed`: The result of the tool execution.

```php
$result = $weatherTool->execute($context, ['location' => 'Paris']);
echo $result; // "The weather in Paris is sunny and 75°F."
```

### Getter Methods

#### `getName(): string`

Gets the name of the tool.

```php
$name = $tool->getName();
```

#### `getDescription(): string`

Gets the description of the tool.

```php
$description = $tool->getDescription();
```

#### `getParameters(): array`

Gets the parameters schema for the tool.

```php
$parameters = $tool->getParameters();
```

## Parameter Schema

The parameters schema defines the inputs that the tool accepts. It follows the JSON Schema format:

```php
$parameters = [
    'type' => 'object',
    'properties' => [
        'location' => [
            'type' => 'string',
            'description' => 'The location to get weather for'
        ]
    ],
    'required' => ['location']
];

$weatherTool = new Tool(
    'get_weather',
    'Get the current weather for a location',
    function (RunContext $context, array $args) {
        $location = $args['location'] ?? 'Unknown';
        return "The weather in $location is sunny and 75°F.";
    },
    $parameters
);
```

If the parameters schema is not provided, it will be inferred from the callback function's signature.

## Helper Functions

The package provides helper functions to simplify creating tools:

### `function_tool(callable $callback, ?string $name = null, ?string $description = null): Tool`

Creates a tool from a function.

```php
use function OpenAI\Agents\function_tool;

function getWeather(RunContext $context, array $args): string
{
    $location = $args['location'] ?? 'Unknown';
    return "The weather in $location is sunny and 75°F.";
}

$weatherTool = function_tool('getWeather', 'Get the current weather for a location');
```

### `function_tool_decorator(?string $nameOverride = null, ?string $descriptionOverride = null, ?array $parametersOverride = null): Closure`

Creates a function that creates a tool (similar to a Python decorator).

```php
use function OpenAI\Agents\function_tool_decorator;

$weatherTool = function_tool_decorator(
    'get_weather',
    'Get the current weather for a location'
)(function (RunContext $context, array $args) {
    $location = $args['location'] ?? 'Unknown';
    return "The weather in $location is sunny and 75°F.";
});
```