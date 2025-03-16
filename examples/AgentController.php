<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Tools\Tool;

class AgentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Define some tools
        $getWeatherTool = new Tool(
            'getWeather',
            'Get the weather for a city',
            function (RunContext $context, array $args) {
                $city = $args['city'] ?? 'Unknown';
                return "The weather in {$city} is sunny.";
            }
        );
        
        $getTimeTool = new Tool(
            'getTime',
            'Get the current time',
            function (RunContext $context, array $args) {
                $timezone = $args['timezone'] ?? 'UTC';
                $date = new \DateTime('now', new \DateTimeZone($timezone));
                return "The current time in {$timezone} is " . $date->format('H:i:s');
            }
        );

        // Create an agent with tools
        $agent = Agent::create(
            "Assistant",
            "You are a helpful assistant that can provide weather and time information."
        )->withTools([$getWeatherTool, $getTimeTool]);

        // Run the agent with the user's message
        $result = Runner::runSync($agent, $request->input('message'));

        // Return the response
        return response()->json([
            'message' => $result->getTextOutput(),
        ]);
    }
    
    /**
     * Handle a streaming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function stream(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Create a simple agent
        $agent = Agent::create(
            "Assistant",
            "You are a helpful assistant."
        );

        // Run the agent in streaming mode
        $streamingResult = Runner::runStreamed($agent, $request->input('message'));

        return response()->stream(function () use ($streamingResult) {
            // Stream the response to the client
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