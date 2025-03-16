# Runner

The `Runner` class is responsible for executing agent workflows. It manages the agent loop, handles tool calls, and processes handoffs.

## Basic Usage

```php
use OpenAI\Agents\Runner;
use OpenAI\Agents\Agent;
use OpenAI\Agents\Models\OpenAIProvider;
use OpenAI\Agents\Tracing\Trace;

$agent = new Agent("Assistant", "You are a helpful assistant");
$modelProvider = new OpenAIProvider(config('agents.api_key'));
$trace = new Trace();
$runner = new Runner($modelProvider, $trace);

$result = $runner->run($agent, "What is the capital of France?");
echo $result->getTextOutput();
```

Or using the Facade:

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create("Assistant", "You are a helpful assistant");
$result = Runner::runSync($agent, "What is the capital of France?");
echo $result->getTextOutput();
```

## Constructor

### `__construct(ModelProviderInterface $modelProvider, Trace $trace)`

Creates a new runner instance.

#### Parameters

- `$modelProvider` (ModelProviderInterface): The model provider to use for agent runs.
- `$trace` (Trace): The trace manager for recording trace data.

## Methods

### `run(Agent $startingAgent, $input, $context = null, ?int $maxTurns = null, ?RunConfig $runConfig = null): RunResult`

Runs a workflow starting at the given agent.

#### Parameters

- `$startingAgent` (Agent): The starting agent to run.
- `$input` (string|array): The initial input to the agent. Can be a string or an array of message items.
- `$context` (mixed|null): Optional context object that will be passed to tools and guardrails.
- `$maxTurns` (int|null): The maximum number of turns to run the agent for. Defaults to 10.
- `$runConfig` (RunConfig|null): Optional configuration for the run.

#### Returns

- `RunResult`: An object containing the results of the run.

#### Exceptions

- `MaxTurnsExceeded`: Thrown when the maximum number of turns is exceeded.
- `InputGuardrailTripwireTriggered`: Thrown when an input guardrail tripwire is triggered.
- `OutputGuardrailTripwireTriggered`: Thrown when an output guardrail tripwire is triggered.
- `AgentsException`: Base exception for all agent-related exceptions.

```php
try {
    $result = $runner->run($agent, "What is the capital of France?");
    echo $result->getTextOutput();
} catch (MaxTurnsExceeded $e) {
    echo "The agent took too many turns to complete the task.";
} catch (InputGuardrailTripwireTriggered $e) {
    echo "Input guardrail triggered: " . $e->getResult()->output->message;
} catch (OutputGuardrailTripwireTriggered $e) {
    echo "Output guardrail triggered: " . $e->getResult()->output->message;
} catch (AgentsException $e) {
    echo "An error occurred: " . $e->getMessage();
}
```

### `runStreamed(Agent $startingAgent, $input, $context = null, ?int $maxTurns = null, ?RunConfig $runConfig = null): RunResultStreaming`

Runs a workflow starting at the given agent in streaming mode.

#### Parameters

Same as `run()`.

#### Returns

- `RunResultStreaming`: An object that provides methods to stream the agent's responses.

```php
$streamingResult = $runner->runStreamed($agent, "Tell me a story about a robot.");

$streamingResult->stream(
    function ($token) {
        echo $token;
        flush();
    },
    function ($toolCall) {
        echo "Tool call: " . json_encode($toolCall) . "\n";
        flush();
    },
    function ($toolResult) {
        echo "Tool result: " . $toolResult . "\n";
        flush();
    },
    function ($newAgent) {
        echo "Handoff to: " . $newAgent->getName() . "\n";
        flush();
    },
    function ($finalOutput) {
        echo "Complete: " . $finalOutput . "\n";
        flush();
    }
);
```

## Facade Methods

In addition to the above methods, the Runner facade provides the following convenience methods:

### `runSync(Agent $startingAgent, $input, $context = null, ?int $maxTurns = null, ?RunConfig $runConfig = null): RunResult`

A synchronous version of `run()` that uses the underlying Runner implementation.

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create("Assistant", "You are a helpful assistant");
$result = Runner::runSync($agent, "What is the capital of France?");
echo $result->getTextOutput();
```