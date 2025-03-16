<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenAI\Agents\Agent;
use OpenAI\Agents\Runner;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Models\OpenAIProvider;
use OpenAI\Agents\Tracing\Trace;
use OpenAI\Agents\RunContext;

// Initialize components
$apiKey = getenv('OPENAI_API_KEY');
$modelProvider = new OpenAIProvider($apiKey);
$trace = new Trace();
$runner = new Runner($modelProvider, $trace);

// Define a function tool
function getWeather(RunContext $context, array $args): string
{
    $city = $args['city'] ?? 'Unknown';
    return "The weather in {$city} is sunny.";
}

// Create an agent with a tool
$agent = new Agent(
    "Hello world",
    "You are a helpful agent."
);
$agent->withTools([function_tool('getWeather', 'Get the weather for a city')]);

// Run the agent
$result = $runner->run($agent, "What's the weather in Tokyo?");

// Print the result
echo $result->getTextOutput() . PHP_EOL;