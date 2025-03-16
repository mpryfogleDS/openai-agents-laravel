# Models

The OpenAI Agents for Laravel package provides a flexible way to work with language models. By default, it uses OpenAI's models, but the architecture is designed to accommodate other model providers as well.

## Using models

### Default model

By default, the package uses the model specified in your configuration file (`config/agents.php`):

```php
'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
```

### Specifying models for agents

You can specify which model an agent should use:

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create("Coding Assistant", "You help write and fix code.")
    ->withModel("gpt-4o");
```

### Model settings

You can customize the model parameters using the `ModelSettings` class:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Models\ModelSettings;

$modelSettings = new ModelSettings(
    0.7,    // temperature
    1.0,    // top_p
    0.0,    // frequency_penalty
    0.0,    // presence_penalty
    4000,   // max_tokens
    60      // timeout in seconds
);

$agent = Agent::create("Creative Writer", "You are a creative fiction writer.")
    ->withModelSettings($modelSettings);
```

Alternatively, you can use method chaining to set specific parameters:

```php
use OpenAI\Agents\Models\ModelSettings;

$modelSettings = new ModelSettings()
    ->withTemperature(0.7)
    ->withTopP(1.0)
    ->withMaxTokens(4000);

$agent->withModelSettings($modelSettings);
```

### Global model settings

You can also set a global model for all agents in a run:

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Models\ModelSettings;

$runConfig = new RunConfig();
$runConfig->withModel("gpt-4o")
    ->withModelSettings(new ModelSettings(0.5));

$result = Runner::runSync($agent, "Write a short story", null, null, $runConfig);
```

## Model parameters

### Temperature

Controls the randomness of the model's outputs. Higher values (e.g., 0.8) make the output more random, while lower values (e.g., 0.2) make it more deterministic.

```php
$modelSettings = new ModelSettings(0.7);
// or
$modelSettings = new ModelSettings()->withTemperature(0.7);
```

### Top P (nucleus sampling)

An alternative to temperature, controls diversity via nucleus sampling. We usually recommend altering temperature rather than top_p.

```php
$modelSettings = new ModelSettings(null, 0.9);
// or
$modelSettings = new ModelSettings()->withTopP(0.9);
```

### Frequency penalty

Reduces the likelihood of repetition by penalizing tokens that have already appeared in the text.

```php
$modelSettings = new ModelSettings(null, null, 0.5);
// or
$modelSettings = new ModelSettings()->withFrequencyPenalty(0.5);
```

### Presence penalty

Reduces the likelihood of repetition by penalizing tokens that have already appeared in the text, regardless of how many times they've appeared.

```php
$modelSettings = new ModelSettings(null, null, null, 0.5);
// or
$modelSettings = new ModelSettings()->withPresencePenalty(0.5);
```

### Max tokens

Controls the maximum number of tokens the model can generate.

```php
$modelSettings = new ModelSettings(null, null, null, null, 4000);
// or
$modelSettings = new ModelSettings()->withMaxTokens(4000);
```

### Timeout

Controls how long to wait for a response from the model (in seconds).

```php
$modelSettings = new ModelSettings(null, null, null, null, null, 60);
// or
$modelSettings = new ModelSettings()->withTimeout(60);
```

## Available models

The package supports OpenAI's models, but not all models support all features. Here are the recommended models:

- **gpt-4o** (default): The most capable model, supports all features including function calling
- **gpt-4-turbo**: A powerful model with broad capabilities
- **gpt-3.5-turbo**: A faster, more economical model suitable for simpler tasks

## Using a custom model provider

The package is designed to allow for custom model providers. To implement a custom provider:

1. Create a class that implements the `ModelProviderInterface`:

```php
namespace App\Services;

use OpenAI\Agents\Models\ModelProviderInterface;
use OpenAI\Agents\Models\Model;

class CustomModelProvider implements ModelProviderInterface
{
    public function getModel(?string $modelName = null): Model
    {
        // Return an instance of your custom model implementation
        return new CustomModel($modelName ?? 'default-model');
    }
}
```

2. Create a class that implements the `Model` interface:

```php
namespace App\Services;

use OpenAI\Agents\Models\Model;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Items\ModelResponse;

class CustomModel implements Model
{
    protected string $modelName;
    
    public function __construct(string $modelName)
    {
        $this->modelName = $modelName;
    }
    
    public function getResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): ModelResponse {
        // Implementation for your custom model
    }
    
    public function streamResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): \Generator {
        // Implementation for streaming from your custom model
    }
}
```

3. Register your custom provider in your service provider:

```php
use App\Services\CustomModelProvider;
use OpenAI\Agents\Models\ModelProviderInterface;

public function register(): void
{
    $this->app->singleton(ModelProviderInterface::class, function ($app) {
        return new CustomModelProvider();
    });
}
```

## Next steps

- Learn about [Tracing](tracing.md)
- Learn about [Context](context.md)
- Learn about [Results](results.md)