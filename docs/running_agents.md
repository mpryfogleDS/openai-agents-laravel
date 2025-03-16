# Running Agents

This guide covers the various ways you can run agents in your Laravel application.

## Running an agent synchronously

The simplest way to run an agent is synchronously, which will block until the agent completes:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant.");

// Run the agent with a user message
$result = Runner::runSync($agent, "What is the capital of France?");

// Get the final output
echo $result->getTextOutput();
```

## Running an agent asynchronously

For web applications, you may want to run agents asynchronously. In Laravel, you can use jobs for this:

```php
use OpenAI\Agents\Agent;
use OpenAI\Agents\Runner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $agent;
    protected $input;
    protected $userId;

    public function __construct(Agent $agent, string $input, int $userId)
    {
        $this->agent = $agent;
        $this->input = $input;
        $this->userId = $userId;
    }

    public function handle(Runner $runner)
    {
        $result = $runner->run($this->agent, $this->input);
        
        // Store the result or notify the user
        // For example:
        \App\Models\AgentResponse::create([
            'user_id' => $this->userId,
            'input' => $this->input,
            'output' => $result->getTextOutput(),
        ]);
    }
}

// In a controller:
public function askAgent(Request $request)
{
    $agent = Agent::create("Assistant", "You are a helpful assistant.");
    
    RunAgentJob::dispatch($agent, $request->input('message'), auth()->id());
    
    return response()->json(['message' => 'Your request is being processed']);
}
```

## Running agents with streaming

For a more interactive experience, you can stream the agent's responses:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

public function streamResponse(Request $request)
{
    $agent = Agent::create("Assistant", "You are a helpful assistant.");
    
    $streamingResult = Runner::runStreamed($agent, $request->input('message'));
    
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
}
```

## Using the RunConfig

You can customize the agent run with a `RunConfig`:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Models\ModelSettings;

$agent = Agent::create("Assistant", "You are a helpful assistant.");

$runConfig = new RunConfig();
$runConfig->withModel("gpt-4o")
    ->withModelSettings(new ModelSettings(0.7, 1.0, 0.0, 0.0, 4000))
    ->withWorkflowName("Customer Support")
    ->withTraceIncludeSensitiveData(true);

$result = Runner::runSync($agent, "What is the capital of France?", null, 5, $runConfig);
```

## Providing context

You can provide additional context to the agent run:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Product Recommender", "You recommend products based on user preferences.");

// Create a context with user data
$context = [
    'user' => [
        'name' => 'John Doe',
        'preferences' => ['electronics', 'books', 'outdoor gear'],
        'purchase_history' => [
            ['product' => 'Smartphone', 'category' => 'electronics'],
            ['product' => 'Hiking Boots', 'category' => 'outdoor gear'],
        ],
    ],
    'products' => [
        // Array of products available
    ],
];

// Run the agent with the context
$result = Runner::runSync($agent, "What products would you recommend for me?", $context);
```

## Processing the result

The `RunResult` object contains details about the agent run:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant.");
$result = Runner::runSync($agent, "What is the capital of France?");

// Get the text output
$textOutput = $result->getTextOutput();

// Get the final output (might be an object if output_type is set)
$finalOutput = $result->getFinalOutput();

// Get all the items generated during the run
$items = $result->getNewItems();

// Get the raw responses from the LLM
$rawResponses = $result->getRawResponses();

// Get the last agent that ran (useful with handoffs)
$lastAgent = $result->getLastAgent();

// Get guardrail results
$inputGuardrailResults = $result->getInputGuardrailResults();
$outputGuardrailResults = $result->getOutputGuardrailResults();
```

## Maximum turns

By default, agents can take up to 10 turns (each turn is one LLM call plus any tool calls). You can modify this limit:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant.");

// Set a maximum of 5 turns
$result = Runner::runSync($agent, "What is the capital of France?", null, 5);
```

## Handling exceptions

Agent runs can throw various exceptions:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Exceptions\MaxTurnsExceeded;
use OpenAI\Agents\Exceptions\InputGuardrailTripwireTriggered;
use OpenAI\Agents\Exceptions\OutputGuardrailTripwireTriggered;

$agent = Agent::create("Assistant", "You are a helpful assistant.");

try {
    $result = Runner::runSync($agent, "What is the capital of France?");
    echo $result->getTextOutput();
} catch (MaxTurnsExceeded $e) {
    echo "The agent took too many turns to complete the task.";
} catch (InputGuardrailTripwireTriggered $e) {
    echo "Input guardrail triggered: " . $e->getResult()->output->message;
} catch (OutputGuardrailTripwireTriggered $e) {
    echo "Output guardrail triggered: " . $e->getResult()->output->message;
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
```

## Next steps

- Learn about [Tools](tools.md)
- Learn about [Handoffs](handoffs.md)
- Learn about [Guardrails](guardrails.md)
- Learn about [Tracing](tracing.md)