# Tools

Tools allow agents to take actions beyond generating text responses. They enable agents to interact with external systems, perform calculations, query databases, and more.

## Creating tools

There are several ways to create tools in OpenAI Agents for Laravel:

### Using the Tool class

The most direct way is to use the `Tool` class:

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

### Using the function_tool helper

You can use the `function_tool` helper to convert any PHP function into a tool:

```php
function getWeather(RunContext $context, array $args): string
{
    $location = $args['location'] ?? 'Unknown';
    
    // In a real implementation, you would call a weather API
    return "The weather in $location is sunny and 75°F.";
}

$weatherTool = function_tool('getWeather', 'Get the current weather for a location');
```

### Using a class method

You can also use class methods as tools:

```php
class WeatherService
{
    public function getWeather(RunContext $context, array $args): string
    {
        $location = $args['location'] ?? 'Unknown';
        
        // In a real implementation, you would call a weather API
        return "The weather in $location is sunny and 75°F.";
    }
}

$weatherService = new WeatherService();
$weatherTool = function_tool([$weatherService, 'getWeather'], 'Get the current weather for a location');
```

## Tool parameters

Tools can define specific parameters they accept:

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

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
    },
    [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'The mathematical expression to evaluate'
            ]
        ],
        'required' => ['expression']
    ]
);
```

If you don't provide a parameters schema, the tool will attempt to infer one based on the function signature.

## Adding tools to agents

You can add one or more tools to an agent:

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create(
    "Research Assistant",
    "You are a research assistant that can search for information and perform calculations."
)->withTools([$searchTool, $calculatorTool]);
```

## Using context in tools

Tools receive a RunContext object that contains any context provided during the agent run:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;

$databaseTool = new Tool(
    'query_database',
    'Query the database for product information',
    function (RunContext $context, array $args) {
        $productId = $args['product_id'] ?? '';
        
        // Access the database connection from context
        $db = $context->getContext()['database'];
        
        // Query the database
        $product = $db->table('products')->find($productId);
        
        if (!$product) {
            return "Product not found.";
        }
        
        return "Product: {$product->name}, Price: \${$product->price}, Stock: {$product->stock}";
    }
);

$agent = Agent::create("Product Assistant", "You help customers find product information.")
    ->withTools([$databaseTool]);

// Run the agent with a database connection in the context
$context = [
    'database' => \DB::connection(),
];

$result = Runner::runSync($agent, "What is the price of product ID 12345?", $context);
```

## Transforming agents into tools

You can transform an agent into a tool, which can then be used by other agents:

```php
use OpenAI\Agents\Facades\Agent;

// Create a specialized agent for weather forecasts
$weatherAgent = Agent::create(
    "Weather Expert",
    "You are an expert on weather forecasts. Provide detailed weather information for any location."
);

// Convert the weather agent into a tool
$weatherTool = $weatherAgent->asTool(
    "get_weather_forecast",
    "Get a detailed weather forecast for a location"
);

// Create a main agent that uses the weather tool
$mainAgent = Agent::create(
    "Travel Assistant",
    "You help plan trips and provide travel recommendations."
)->withTools([$weatherTool, $flightSearchTool, $hotelSearchTool]);
```

## Example tools

### Database query tool

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;
use Illuminate\Support\Facades\DB;

$queryDatabaseTool = new Tool(
    'query_database',
    'Query the database with SQL',
    function (RunContext $context, array $args) {
        $query = $args['query'] ?? '';
        
        // IMPORTANT: In a production environment, you should validate and sanitize SQL queries
        // This is just a simple example
        try {
            $results = DB::select($query);
            return json_encode($results);
        } catch (\Exception $e) {
            return "Error executing query: " . $e->getMessage();
        }
    }
);
```

### File reading tool

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;
use Illuminate\Support\Facades\Storage;

$readFileTool = new Tool(
    'read_file',
    'Read the contents of a file',
    function (RunContext $context, array $args) {
        $path = $args['path'] ?? '';
        
        if (!Storage::exists($path)) {
            return "File not found: $path";
        }
        
        return Storage::get($path);
    }
);
```

### API request tool

```php
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\RunContext;
use Illuminate\Support\Facades\Http;

$apiRequestTool = new Tool(
    'api_request',
    'Make an HTTP request to an API',
    function (RunContext $context, array $args) {
        $url = $args['url'] ?? '';
        $method = strtoupper($args['method'] ?? 'GET');
        $data = $args['data'] ?? [];
        
        try {
            $response = match($method) {
                'GET' => Http::get($url),
                'POST' => Http::post($url, $data),
                'PUT' => Http::put($url, $data),
                'DELETE' => Http::delete($url, $data),
                default => null
            };
            
            if (!$response) {
                return "Unsupported HTTP method: $method";
            }
            
            return $response->body();
        } catch (\Exception $e) {
            return "Error making API request: " . $e->getMessage();
        }
    }
);
```

## Next steps

- Learn about [Handoffs](handoffs.md)
- Learn about [Guardrails](guardrails.md)
- Learn about [Models](models.md)