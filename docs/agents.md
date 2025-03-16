# Agents

An agent in OpenAI Agents for Laravel is a configurable entity that can process natural language inputs, use tools, and generate outputs. Agents are powered by LLMs (like GPT-4) and are configured with specific instructions, tools, and other settings.

## Creating an agent

You can create an agent using the `Agent` facade:

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create(
    "Customer Support",
    "You are a helpful customer support agent for a clothing retailer. Help customers with inquiries about products, orders, and returns."
);
```

Or directly using the `Agent` class:

```php
use OpenAI\Agents\Agent;

$agent = new Agent(
    "Customer Support",
    "You are a helpful customer support agent for a clothing retailer. Help customers with inquiries about products, orders, and returns."
);
```

## Agent configuration

### Instructions

The instructions (also known as the system prompt) define the agent's behavior and capabilities. They should clearly describe what the agent should do and how it should respond.

```php
$agent = Agent::create(
    "Math Tutor",
    "You are a math tutor who helps students understand mathematical concepts. 
     Be patient, provide step-by-step explanations, and give examples to illustrate concepts.
     When a student makes a mistake, gently guide them to the correct approach rather than simply giving the answer."
);
```

### Tools

Tools allow agents to perform actions beyond just generating text. You can add tools to an agent using the `withTools` method:

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

// Create a calculator tool
$calculatorTool = new Tool(
    'calculate',
    'Perform a mathematical calculation',
    function (RunContext $context, array $args) {
        $expression = $args['expression'] ?? '';
        // Implementation of calculator logic
        return "Result: $result";
    }
);

// Add the tool to the agent
$agent = Agent::create("Calculator Agent", "You help with math calculations.")
    ->withTools([$calculatorTool]);
```

### Handoffs

Handoffs allow agents to transfer control to other agents for specific tasks:

```php
// Create specialized agents
$productAgent = Agent::create(
    "Product Specialist",
    "You provide detailed information about products."
);

$returnsAgent = Agent::create(
    "Returns Specialist",
    "You handle product return inquiries and processes."
);

// Create a main agent that can hand off to specialized agents
$mainAgent = Agent::create(
    "Customer Support",
    "You triage customer inquiries and hand off to specialists when appropriate."
)->withHandoffs([$productAgent, $returnsAgent]);
```

### Model selection

You can specify which model an agent should use:

```php
use OpenAI\Agents\Models\ModelSettings;

$agent = Agent::create("Coding Assistant", "You help write and fix code.")
    ->withModel("gpt-4o");

// Or with custom model settings
$modelSettings = new ModelSettings(
    0.5,   // temperature
    0.9,   // top_p
    0.0,   // frequency_penalty
    0.0,   // presence_penalty
    4000,  // max_tokens
    60     // timeout in seconds
);

$agent = Agent::create("Coding Assistant", "You help write and fix code.")
    ->withModelSettings($modelSettings);
```

### Guardrails

Guardrails allow you to enforce constraints on agent inputs and outputs:

```php
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;

// Create a custom input guardrail
class ContentFilterGuardrail extends InputGuardrail
{
    public function check($input, $context, $agent): GuardrailOutput
    {
        // Check for inappropriate content
        if (strpos(strtolower($input), 'inappropriate-word') !== false) {
            return GuardrailOutput::failed("Input contains inappropriate content");
        }
        
        return GuardrailOutput::passed();
    }
}

// Add the guardrail to the agent
$agent = Agent::create("Safe Assistant", "You are a helpful assistant.")
    ->withInputGuardrails([new ContentFilterGuardrail()]);
```

### Output types

You can specify the expected output type for an agent:

```php
$agent = Agent::create("Product Recommender", "You recommend products based on customer preferences.")
    ->withOutputType("json");
```

## Cloning agents

You can create a copy of an agent with modified attributes:

```php
$baseAgent = Agent::create("Base Agent", "You are a helpful assistant.");

// Create a specialized agent based on the base agent
$specializedAgent = $baseAgent->clone([
    'name' => 'Specialized Agent',
    'instructions' => 'You are a specialized assistant for technical topics.',
]);
```

## Using agents as tools

You can transform an agent into a tool that other agents can use:

```php
$calculatorAgent = Agent::create(
    "Calculator",
    "You perform mathematical calculations accurately."
);

$calculatorTool = $calculatorAgent->asTool(
    "calculate",
    "Solve complex mathematical problems"
);

$mainAgent = Agent::create("Math Tutor", "You help students with math homework.")
    ->withTools([$calculatorTool]);
```

## Next steps

- Learn about [Running Agents](running_agents.md)
- Learn about [Tools](tools.md)
- Learn about [Handoffs](handoffs.md)
- Learn about [Guardrails](guardrails.md)