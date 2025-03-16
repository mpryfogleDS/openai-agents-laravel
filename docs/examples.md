# Examples

This page contains a variety of examples showing how to use the OpenAI Agents for Laravel package in different scenarios.

## Basic agent

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

$agent = Agent::create("Assistant", "You are a helpful assistant.");

$result = Runner::runSync($agent, "What is the capital of France?");
echo $result->getTextOutput();
// Output: "The capital of France is Paris."
```

## Using tools

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

// Create a weather tool
$weatherTool = new Tool(
    'get_weather',
    'Get the current weather for a location',
    function (RunContext $context, array $args) {
        $location = $args['location'] ?? 'Unknown';
        
        // In a real implementation, you would call a weather API
        $weatherData = [
            'New York' => ['condition' => 'Sunny', 'temperature' => 75],
            'London' => ['condition' => 'Rainy', 'temperature' => 60],
            'Tokyo' => ['condition' => 'Cloudy', 'temperature' => 70],
            'Paris' => ['condition' => 'Partly Cloudy', 'temperature' => 65],
        ];
        
        $weather = $weatherData[$location] ?? ['condition' => 'Unknown', 'temperature' => 0];
        
        return "The weather in {$location} is {$weather['condition']} with a temperature of {$weather['temperature']}°F.";
    }
);

// Create an agent with the weather tool
$agent = Agent::create(
    "Weather Assistant",
    "You are a helpful assistant that can provide weather information."
)->withTools([$weatherTool]);

// Run the agent
$result = Runner::runSync($agent, "What's the weather like in Tokyo?");
echo $result->getTextOutput();
// Output: "The weather in Tokyo is Cloudy with a temperature of: 70°F."
```

## Handoffs between agents

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

// Create specialized agents
$spanishAgent = Agent::create(
    "Spanish Agent",
    "You only speak Spanish. Be friendly and helpful."
);

$englishAgent = Agent::create(
    "English Agent",
    "You only speak English. Be friendly and helpful."
);

// Create a triage agent
$triageAgent = Agent::create(
    "Triage Agent",
    "You detect the language of the user and handoff to the appropriate agent."
)->withHandoffs([$spanishAgent, $englishAgent]);

// Run the agent with a Spanish message
$result = Runner::runSync($triageAgent, "Hola, ¿cómo estás hoy?");
echo $result->getTextOutput();
// Output (in Spanish): "¡Hola! Estoy muy bien, gracias por preguntar. ¿Cómo estás tú hoy?"

// Run the agent with an English message
$result = Runner::runSync($triageAgent, "Hello, how are you today?");
echo $result->getTextOutput();
// Output (in English): "Hello! I'm doing well, thank you for asking. How are you today?"
```

## Using guardrails

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Agent as AgentClass;

// Create a content filter guardrail
class ContentFilterGuardrail extends InputGuardrail
{
    protected $bannedWords = ['inappropriate', 'offensive', 'harmful'];
    
    public function check($input, RunContext $context, AgentClass $agent): GuardrailOutput
    {
        // Convert input to string if it's an array
        $text = is_array($input) ? json_encode($input) : $input;
        
        foreach ($this->bannedWords as $word) {
            if (stripos($text, $word) !== false) {
                return GuardrailOutput::failed("Input contains banned word: $word");
            }
        }
        
        return GuardrailOutput::passed("Input passed content filter");
    }
}

// Create an agent with the content filter guardrail
$agent = Agent::create("Safe Assistant", "You are a helpful assistant.")
    ->withInputGuardrails([new ContentFilterGuardrail()]);

try {
    // This will pass the guardrail
    $result = Runner::runSync($agent, "What is the capital of France?");
    echo $result->getTextOutput();
    
    // This will trigger the guardrail
    $result = Runner::runSync($agent, "Tell me something inappropriate.");
} catch (\OpenAI\Agents\Exceptions\InputGuardrailTripwireTriggered $e) {
    echo "Guardrail triggered: " . $e->getResult()->output->message;
    // Output: "Guardrail triggered: Input contains banned word: inappropriate"
}
```

## Using context

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

// Create a database query tool
$queryDatabaseTool = new Tool(
    'query_database',
    'Query the database for information',
    function (RunContext $context, array $args) {
        $query = $args['query'] ?? '';
        
        // Access the database connection from context
        $db = $context->getContext()['database'];
        
        // Execute the query (simplified for example)
        $results = $db->select($query);
        
        return json_encode($results);
    }
);

// Create an agent with the database query tool
$agent = Agent::create(
    "Database Assistant",
    "You are a helpful assistant that can query a database. Be careful with your SQL queries."
)->withTools([$queryDatabaseTool]);

// Run the agent with a database connection in the context
$context = [
    'database' => DB::connection(),
];

$result = Runner::runSync($agent, "How many users are in the database?", $context);
echo $result->getTextOutput();
// Output: "There are 1,234 users in the database."
```

## Using streaming

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

class StreamController extends Controller
{
    public function stream(Request $request)
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
}
```

## Customer support bot

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

// Create tools for the customer support bot
$orderStatusTool = new Tool(
    'get_order_status',
    'Get the status of an order',
    function (RunContext $context, array $args) {
        $orderId = $args['order_id'] ?? '';
        
        // Get the user ID from context
        $userId = $context->getContext()['user_id'] ?? null;
        
        // In a real implementation, you would query a database
        $orderStatuses = [
            '12345' => ['status' => 'Shipped', 'delivery_date' => '2025-03-20'],
            '67890' => ['status' => 'Processing', 'delivery_date' => null],
        ];
        
        $order = $orderStatuses[$orderId] ?? null;
        
        if (!$order) {
            return "Order not found.";
        }
        
        if ($order['status'] === 'Shipped') {
            return "Your order #{$orderId} has been shipped and is expected to be delivered on {$order['delivery_date']}.";
        } else {
            return "Your order #{$orderId} is currently {$order['status']}.";
        }
    }
);

$returnItemTool = new Tool(
    'create_return',
    'Create a return request for an order',
    function (RunContext $context, array $args) {
        $orderId = $args['order_id'] ?? '';
        $reason = $args['reason'] ?? '';
        
        // In a real implementation, you would update a database
        return "A return request for order #{$orderId} has been created. Reason: {$reason}. You will receive return instructions via email within 24 hours.";
    }
);

$trackPackageTool = new Tool(
    'track_package',
    'Track a package',
    function (RunContext $context, array $args) {
        $trackingNumber = $args['tracking_number'] ?? '';
        
        // In a real implementation, you would call a shipping API
        $trackingInfo = [
            'ABC123456' => ['status' => 'In Transit', 'location' => 'Distribution Center', 'eta' => '2025-03-19'],
            'XYZ789012' => ['status' => 'Delivered', 'location' => 'Front Door', 'delivery_date' => '2025-03-15'],
        ];
        
        $tracking = $trackingInfo[$trackingNumber] ?? null;
        
        if (!$tracking) {
            return "Tracking information not found for #{$trackingNumber}.";
        }
        
        if ($tracking['status'] === 'Delivered') {
            return "Package #{$trackingNumber} was delivered to {$tracking['location']} on {$tracking['delivery_date']}.";
        } else {
            return "Package #{$trackingNumber} is currently {$tracking['status']} at {$tracking['location']}. Expected delivery: {$tracking['eta']}.";
        }
    }
);

// Create specialized agents
$orderAgent = Agent::create(
    "Order Specialist",
    "You are a specialist for order-related inquiries. Help customers check order status, track packages, and process returns."
)->withTools([$orderStatusTool, $trackPackageTool, $returnItemTool])
 ->withHandoffDescription("Handles order-related inquiries");

$productAgent = Agent::create(
    "Product Specialist",
    "You are a specialist for product-related inquiries. Help customers with product information, features, and compatibility."
)->withHandoffDescription("Handles product-related inquiries");

// Create a main triage agent
$customerSupportAgent = Agent::create(
    "Customer Support",
    "You are a customer support agent. Help triage customer inquiries and hand off to specialists when appropriate."
)->withHandoffs([$orderAgent, $productAgent]);

// Run the agent with user context
$context = [
    'user_id' => 12345,
    'name' => 'John Doe',
];

$result = Runner::runSync($customerSupportAgent, "I haven't received my order #12345 yet. Can you check the status?", $context);
echo $result->getTextOutput();
// Output: "Your order #12345 has been shipped and is expected to be delivered on 2025-03-20."
```

## Next steps

For more examples, check out the [examples directory](https://github.com/your-username/openai-agents-laravel/tree/main/examples) in the GitHub repository.