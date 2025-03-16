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

// Create agents
$spanishAgent = new Agent(
    "Spanish agent",
    "You only speak Spanish."
);

$englishAgent = new Agent(
    "English agent",
    "You only speak English"
);

$triageAgent = new Agent(
    "Triage agent",
    "Handoff to the appropriate agent based on the language of the request."
);
$triageAgent->withHandoffs([$spanishAgent, $englishAgent]);

// Run the agent
$result = $runner->run($triageAgent, "Hola, ¿cómo estás?");

// Print the result
echo $result->getTextOutput() . PHP_EOL;