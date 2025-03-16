<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenAI\Agents\Agent;
use OpenAI\Agents\Runner;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Models\OpenAIProvider;
use OpenAI\Agents\Tracing\Trace;

// Initialize components
$apiKey = getenv('OPENAI_API_KEY');
$modelProvider = new OpenAIProvider($apiKey);
$trace = new Trace();
$runner = new Runner($modelProvider, $trace);

// Create an agent
$agent = new Agent("Assistant", "You are a helpful assistant");

// Run the agent
$result = $runner->run($agent, "Write a haiku about recursion in programming.");

// Print the result
echo $result->getTextOutput() . PHP_EOL;