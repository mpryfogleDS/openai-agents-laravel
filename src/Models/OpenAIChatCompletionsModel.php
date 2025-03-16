<?php

namespace JawadAshraf\OpenAI\Agents\Models;

use OpenAI\Client;
use Illuminate\Support\Facades\Log;
use OpenAI\Agents\Items\ModelResponse;
use OpenAI\Agents\Items\Usage;
use OpenAI\Exceptions\ErrorException;

class OpenAIChatCompletionsModel implements Model
{
    /**
     * The OpenAI client.
     *
     * @var Client
     */
    protected Client $client;
    
    /**
     * The model name.
     *
     * @var string
     */
    protected string $modelName;
    
    /**
     * Create a new OpenAI chat completions model instance.
     *
     * @param Client $client
     * @param string $modelName
     */
    public function __construct(Client $client, string $modelName)
    {
        $this->client = $client;
        $this->modelName = $modelName;
    }
    
    /**
     * Get a response from the model.
     *
     * @param string|null $systemInstructions
     * @param array $input
     * @param ModelSettings $modelSettings
     * @param array $tools
     * @param string|null $outputType
     * @param array $handoffs
     * @param array $tracing
     * @return ModelResponse
     */
    public function getResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): ModelResponse {
        $messages = $this->prepareMessages($systemInstructions, $input);
        $tools = $this->prepareTools($tools, $handoffs, $outputType);
        $responseFormat = $this->prepareResponseFormat($outputType);
        
        $params = array_merge([
            'model' => $this->modelName,
            'messages' => $messages,
        ], $modelSettings->toArray());
        
        if (!empty($tools)) {
            $params['tools'] = $tools;
        }
        
        if ($responseFormat) {
            $params['response_format'] = $responseFormat;
        }
        
        try {
            $response = $this->client->chat()->create($params);
            
            $usage = new Usage(
                1,
                $response->usage->promptTokens,
                $response->usage->completionTokens,
                $response->usage->totalTokens
            );
            
            $output = $response->choices[0]->message->toArray();
            
            return new ModelResponse($output, $usage, $response->id);
        } catch (ErrorException $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Stream a response from the model.
     *
     * @param string|null $systemInstructions
     * @param array $input
     * @param ModelSettings $modelSettings
     * @param array $tools
     * @param string|null $outputType
     * @param array $handoffs
     * @param array $tracing
     * @return \Generator
     */
    public function streamResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): \Generator {
        $messages = $this->prepareMessages($systemInstructions, $input);
        $tools = $this->prepareTools($tools, $handoffs, $outputType);
        $responseFormat = $this->prepareResponseFormat($outputType);
        
        $params = array_merge([
            'model' => $this->modelName,
            'messages' => $messages,
            'stream' => true,
        ], $modelSettings->toArray());
        
        if (!empty($tools)) {
            $params['tools'] = $tools;
        }
        
        if ($responseFormat) {
            $params['response_format'] = $responseFormat;
        }
        
        try {
            $stream = $this->client->chat()->createStreamed($params);
            
            $responseId = null;
            $content = '';
            $toolCalls = [];
            
            foreach ($stream as $chunk) {
                $delta = $chunk->choices[0]->delta;
                $responseId = $chunk->id;
                
                if (isset($delta->content) && $delta->content !== null) {
                    $content .= $delta->content;
                }
                
                if (isset($delta->toolCalls)) {
                    foreach ($delta->toolCalls as $index => $toolCall) {
                        if (!isset($toolCalls[$index])) {
                            $toolCalls[$index] = [
                                'id' => $toolCall->id ?? null,
                                'type' => $toolCall->type ?? 'function',
                                'function' => [
                                    'name' => $toolCall->function->name ?? '',
                                    'arguments' => $toolCall->function->arguments ?? '',
                                ],
                            ];
                        } else {
                            if (isset($toolCall->function->arguments)) {
                                $toolCalls[$index]['function']['arguments'] .= $toolCall->function->arguments;
                            }
                        }
                    }
                }
                
                // Yield the streaming event
                yield [
                    'type' => 'chunk',
                    'content' => $delta->content ?? null,
                    'toolCalls' => $delta->toolCalls ?? null,
                ];
            }
            
            // Yield a completion event at the end
            $finalOutput = [
                'content' => $content,
            ];
            
            if (!empty($toolCalls)) {
                $finalOutput['tool_calls'] = array_values($toolCalls);
            }
            
            // This estimates tokens as we don't get usage in streaming mode
            $promptTokens = $this->estimateTokenCount(json_encode($messages));
            $completionTokens = $this->estimateTokenCount(json_encode($finalOutput));
            
            $usage = new Usage(
                1,
                $promptTokens,
                $completionTokens,
                $promptTokens + $completionTokens
            );
            
            yield [
                'type' => 'completed',
                'response' => [
                    'id' => $responseId,
                    'output' => $finalOutput,
                    'usage' => $usage,
                ],
            ];
        } catch (ErrorException $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Prepare messages for the API request.
     *
     * @param string|null $systemInstructions
     * @param array $input
     * @return array
     */
    protected function prepareMessages(?string $systemInstructions, array $input): array
    {
        $messages = [];
        
        if ($systemInstructions) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemInstructions,
            ];
        }
        
        foreach ($input as $item) {
            $message = [
                'role' => $item['role'],
            ];
            
            if (isset($item['content'])) {
                $message['content'] = $item['content'];
            } else {
                $message['content'] = null;
            }
            
            if (isset($item['tool_calls']) && !empty($item['tool_calls'])) {
                $message['tool_calls'] = $item['tool_calls'];
            }
            
            if ($item['role'] === 'tool' && isset($item['tool_call_id'])) {
                $message['tool_call_id'] = $item['tool_call_id'];
                
                if (isset($item['name'])) {
                    $message['name'] = $item['name'];
                }
            }
            
            $messages[] = $message;
        }
        
        return $messages;
    }
    
    /**
     * Prepare tools for the API request.
     *
     * @param array $tools
     * @param array $handoffs
     * @param string|null $outputType
     * @return array
     */
    protected function prepareTools(array $tools, array $handoffs, ?string $outputType): array
    {
        $result = [];
        
        // Add function tools
        foreach ($tools as $tool) {
            $result[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => $tool->getParameters(),
                ],
            ];
        }
        
        // Add handoff tools
        foreach ($handoffs as $handoff) {
            $result[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'handoff',
                    'description' => 'Handoff to another agent',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'agent_name' => [
                                'type' => 'string',
                                'enum' => [$handoff->getAgentName()],
                                'description' => $handoff->getDescription(),
                            ],
                        ],
                        'required' => ['agent_name'],
                    ],
                ],
            ];
        }
        
        return $result;
    }
    
    /**
     * Prepare response format for the API request.
     *
     * @param string|null $outputType
     * @return array|null
     */
    protected function prepareResponseFormat(?string $outputType): ?array
    {
        if (!$outputType) {
            return null;
        }
        
        return [
            'type' => 'json_object',
        ];
    }
    
    /**
     * Estimate token count for a string.
     *
     * @param string $text
     * @return int
     */
    protected function estimateTokenCount(string $text): int
    {
        // A very rough estimate: ~4 characters per token for English text
        return (int) ceil(strlen($text) / 4);
    }
}