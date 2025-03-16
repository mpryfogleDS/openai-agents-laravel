# Quickstart

## Installation

### Install via Composer

```bash
composer require openai/agents
```

### Publish the configuration file

```bash
php artisan vendor:publish --tag="agents-config"
```

### Set your OpenAI API key

Add your OpenAI API key to your `.env` file:

```
OPENAI_API_KEY=your-api-key
OPENAI_DEFAULT_MODEL=gpt-4o
```

## Create your first agent

Agents can be defined with instructions, a name, and optional configuration.

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create(
    "Math Tutor",
    "You provide help with math problems. Explain your reasoning at each step and include examples"
);
```

## Add a few more agents

Additional agents can be defined in the same way. Handoff descriptions provide additional context for determining handoff routing.

```php
use OpenAI\Agents\Facades\Agent;

$historyTutorAgent = Agent::create(
    "History Tutor",
    "You provide assistance with historical queries. Explain important events and context clearly."
)->withHandoffDescription("Specialist agent for historical questions");

$mathTutorAgent = Agent::create(
    "Math Tutor",
    "You provide help with math problems. Explain your reasoning at each step and include examples"
)->withHandoffDescription("Specialist agent for math questions");
```

## Define your handoffs

On each agent, you can define an inventory of outgoing handoff options that the agent can choose from to decide how to make progress on their task.

```php
$triageAgent = Agent::create(
    "Triage Agent",
    "You determine which agent to use based on the user's homework question"
)->withHandoffs([$historyTutorAgent, $mathTutorAgent]);
```

## Run the agent orchestration

Let's check that the workflow runs and the triage agent correctly routes between the two specialist agents.

```php
use OpenAI\Agents\Facades\Runner;

$result = Runner::runSync($triageAgent, "What is the capital of France?");
echo $result->getTextOutput();
```

## Add a guardrail

You can define custom guardrails to run on the input or output.

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;
use OpenAI\Agents\RunContext;

// Create a custom input guardrail
class HomeworkGuardrail extends InputGuardrail
{
    public function check($input, RunContext $context, $agent): GuardrailOutput
    {
        // Check if the input is a homework question
        if (strpos(strtolower($input), 'homework') !== false) {
            return GuardrailOutput::passed("This is a homework question");
        }
        
        // If it's not about homework, reject it
        return GuardrailOutput::failed("We only answer homework questions");
    }
}

// Add the guardrail to the triage agent
$triageAgent = Agent::create(
    "Triage Agent",
    "You determine which agent to use based on the user's homework question"
)->withHandoffs([$historyTutorAgent, $mathTutorAgent])
 ->withInputGuardrails([new HomeworkGuardrail()]);
```

## Add a tool

Agents can be more powerful when they have tools to work with.

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

// Create a simple calculator tool
$calculatorTool = new Tool(
    'calculate',
    'Perform a mathematical calculation',
    function (RunContext $context, array $args) {
        $expression = $args['expression'] ?? '';
        
        // Only allow basic math operations
        if (preg_match('/^[0-9\+\-\*\/\(\)\s\.]+$/', $expression)) {
            try {
                eval('$result = ' . $expression . ';');
                return "Result: $result";
            } catch (\Throwable $e) {
                return "Error in calculation: " . $e->getMessage();
            }
        }
        
        return "Invalid expression. Only basic math operations are allowed.";
    }
);

// Add the tool to the math tutor agent
$mathTutorAgent = Agent::create(
    "Math Tutor",
    "You provide help with math problems. Explain your reasoning at each step and include examples. Use the calculate tool for complex calculations."
)->withTools([$calculatorTool]);

// Run the agent with the tool
$result = Runner::runSync($mathTutorAgent, "What is 127 * 345?");
echo $result->getTextOutput();
```

## Streaming responses

For a more interactive user experience, you can stream the agent's responses:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant");

// Get a streaming result
$streamingResult = Runner::runStreamed($agent, "Write a short story about a robot learning to paint.");

// In a controller, you can return a streamed response
return response()->stream(function () use ($streamingResult) {
    $streamingResult->stream(
        function ($token) {
            echo "data: " . json_encode(['type' => 'token', 'content' => $token]) . "\n\n";
            ob_flush();
            flush();
        },
        function ($toolCall) {
            echo "data: " . json_encode(['type' => 'tool_call', 'content' => $toolCall]) . "\n\n";
            ob_flush();
            flush();
        },
        function ($toolResult) {
            echo "data: " . json_encode(['type' => 'tool_result', 'content' => $toolResult]) . "\n\n";
            ob_flush();
            flush();
        },
        function ($newAgent) {
            echo "data: " . json_encode(['type' => 'agent_change', 'content' => $newAgent->getName()]) . "\n\n";
            ob_flush();
            flush();
        },
        function ($finalOutput) {
            echo "data: " . json_encode(['type' => 'complete', 'content' => $finalOutput]) . "\n\n";
            ob_flush();
            flush();
        }
    );
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
    'X-Accel-Buffering' => 'no',
]);
```

## Next steps

Learn how to build more complex agentic flows:

- Learn about [Agents](agents.md)
- Learn about [Running Agents](running_agents.md)
- Learn about [Tools](tools.md), [Guardrails](guardrails.md), and [Models](models.md)