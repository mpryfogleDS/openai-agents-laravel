<?php

namespace JawadAshraf\OpenAI\Agents;

use Illuminate\Support\Facades\Log;
use OpenAI\Agents\Models\ModelProviderInterface;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Exceptions\AgentsException;
use OpenAI\Agents\Exceptions\MaxTurnsExceeded;
use OpenAI\Agents\Guardrails\InputGuardrailResult;
use OpenAI\Agents\Guardrails\OutputGuardrailResult;
use OpenAI\Agents\Tracing\Trace;
use OpenAI\Agents\Result\RunResult;
use OpenAI\Agents\Handoffs\Handoff;
use OpenAI\Agents\Items\ModelResponse;
use OpenAI\Agents\Items\RunItem;
use OpenAI\Agents\Result\RunResultStreaming;

class Runner
{
    const DEFAULT_MAX_TURNS = 10;
    
    /**
     * The model provider implementation.
     *
     * @var ModelProviderInterface
     */
    protected ModelProviderInterface $modelProvider;
    
    /**
     * The trace manager
     *
     * @var Trace
     */
    protected Trace $trace;
    
    /**
     * Create a new runner instance.
     *
     * @param ModelProviderInterface $modelProvider
     * @param Trace $trace
     */
    public function __construct(ModelProviderInterface $modelProvider, Trace $trace)
    {
        $this->modelProvider = $modelProvider;
        $this->trace = $trace;
    }
    
    /**
     * Run a workflow starting at the given agent.
     *
     * @param Agent $startingAgent
     * @param string|array $input
     * @param mixed|null $context
     * @param int|null $maxTurns
     * @param RunConfig|null $runConfig
     * @return RunResult
     * @throws AgentsException
     * @throws MaxTurnsExceeded
     */
    public function run(
        Agent $startingAgent,
        $input,
        $context = null,
        ?int $maxTurns = null,
        ?RunConfig $runConfig = null
    ): RunResult {
        $runConfig = $runConfig ?? new RunConfig();
        $maxTurns = $maxTurns ?? config('agents.default_max_turns', self::DEFAULT_MAX_TURNS);
        
        $this->trace->start($runConfig->workflowName, $runConfig->traceId, $runConfig->groupId, $runConfig->traceMetadata);
        
        try {
            $currentTurn = 0;
            $originalInput = $input;
            $generatedItems = [];
            $modelResponses = [];
            
            $contextWrapper = new RunContext($context);
            
            $inputGuardrailResults = [];
            
            $currentAgent = $startingAgent;
            $shouldRunAgentStartHooks = true;
            
            $currentSpan = null;
            
            while (true) {
                // Start an agent span if we don't have one
                if ($currentSpan === null) {
                    $handoffNames = $this->getHandoffNames($currentAgent);
                    $toolNames = $this->getToolNames($currentAgent);
                    $outputTypeName = $currentAgent->getOutputType() ?? 'string';
                    
                    $currentSpan = $this->trace->startAgentSpan(
                        $currentAgent->getName(),
                        $handoffNames,
                        $toolNames,
                        $outputTypeName
                    );
                }
                
                $currentTurn++;
                if ($currentTurn > $maxTurns) {
                    $this->trace->addErrorToSpan(
                        $currentSpan,
                        'Max turns exceeded',
                        ['max_turns' => $maxTurns]
                    );
                    throw new MaxTurnsExceeded("Max turns ({$maxTurns}) exceeded");
                }
                
                Log::debug("Running agent {$currentAgent->getName()} (turn {$currentTurn})");
                
                if ($currentTurn === 1) {
                    // Run input guardrails on the first turn
                    $inputGuardrailResults = $this->runInputGuardrails(
                        $startingAgent,
                        $startingAgent->getInputGuardrails(),
                        $input,
                        $contextWrapper
                    );
                    
                    $turnResult = $this->runSingleTurn(
                        $currentAgent,
                        $originalInput,
                        $generatedItems,
                        $contextWrapper,
                        $runConfig,
                        $shouldRunAgentStartHooks
                    );
                } else {
                    $turnResult = $this->runSingleTurn(
                        $currentAgent,
                        $originalInput,
                        $generatedItems,
                        $contextWrapper,
                        $runConfig,
                        $shouldRunAgentStartHooks
                    );
                }
                
                $shouldRunAgentStartHooks = false;
                
                $modelResponses[] = $turnResult['modelResponse'];
                $originalInput = $turnResult['originalInput'];
                $generatedItems = $turnResult['generatedItems'];
                
                $nextStep = $turnResult['nextStep'];
                
                if ($nextStep['type'] === 'final_output') {
                    $outputGuardrailResults = $this->runOutputGuardrails(
                        $currentAgent->getOutputGuardrails(),
                        $currentAgent,
                        $nextStep['output'],
                        $contextWrapper
                    );
                    
                    $this->trace->finishSpan($currentSpan);
                    
                    return new RunResult(
                        $originalInput,
                        $generatedItems,
                        $modelResponses,
                        $nextStep['output'],
                        $currentAgent,
                        $inputGuardrailResults,
                        $outputGuardrailResults
                    );
                } elseif ($nextStep['type'] === 'handoff') {
                    $currentAgent = $nextStep['newAgent'];
                    $this->trace->finishSpan($currentSpan);
                    $currentSpan = null;
                    $shouldRunAgentStartHooks = true;
                } elseif ($nextStep['type'] === 'run_again') {
                    // Just continue the loop
                } else {
                    throw new AgentsException("Unknown next step type: {$nextStep['type']}");
                }
            }
        } finally {
            if ($currentSpan !== null) {
                $this->trace->finishSpan($currentSpan);
            }
            $this->trace->finish();
        }
    }
    
    /**
     * Run a workflow in streaming mode.
     *
     * @param Agent $startingAgent
     * @param string|array $input
     * @param mixed|null $context
     * @param int|null $maxTurns
     * @param RunConfig|null $runConfig
     * @return RunResultStreaming
     */
    public function runStreamed(
        Agent $startingAgent,
        $input,
        $context = null,
        ?int $maxTurns = null,
        ?RunConfig $runConfig = null
    ): RunResultStreaming {
        $runConfig = $runConfig ?? new RunConfig();
        $maxTurns = $maxTurns ?? config('agents.default_max_turns', self::DEFAULT_MAX_TURNS);
        
        $contextWrapper = new RunContext($context);
        
        $this->trace->start(
            $runConfig->workflowName,
            $runConfig->traceId,
            $runConfig->groupId,
            $runConfig->traceMetadata
        );
        
        $streamedResult = new RunResultStreaming(
            $input,
            [],
            $startingAgent,
            [],
            null,
            false,
            0,
            $maxTurns,
            [],
            []
        );
        
        // We need to run this in async/background in PHP
        // Due to PHP limitations, we'll simulate this by preparing the streaming result
        // and returning it immediately. The caller will have to handle streaming via callbacks
        
        // Initialize the streamed run and return the object
        // The actual streaming happens when the user accesses the streaming methods
        $streamedResult->setRunner($this);
        $streamedResult->setContext($contextWrapper);
        $streamedResult->setRunConfig($runConfig);
        
        return $streamedResult;
    }
    
    /**
     * Run a single turn in the agent loop.
     *
     * @param Agent $agent
     * @param string|array $originalInput
     * @param array $generatedItems
     * @param RunContext $contextWrapper
     * @param RunConfig $runConfig
     * @param bool $shouldRunAgentStartHooks
     * @return array
     */
    protected function runSingleTurn(
        Agent $agent,
        $originalInput,
        array $generatedItems,
        RunContext $contextWrapper,
        RunConfig $runConfig,
        bool $shouldRunAgentStartHooks
    ): array {
        $systemPrompt = $agent->getSystemPrompt($contextWrapper);
        
        $input = $this->prepareInput($originalInput, $generatedItems);
        
        $model = $this->getModel($agent, $runConfig);
        $modelSettings = $this->resolveModelSettings($agent->getModelSettings(), $runConfig->modelSettings);
        
        $response = $model->getResponse(
            $systemPrompt,
            $input,
            $modelSettings,
            $agent->getTools(),
            $agent->getOutputType(),
            $agent->getHandoffs()
        );
        
        // Process the response
        $processedResponse = $this->processModelResponse(
            $agent,
            $response,
            $agent->getOutputType(),
            $agent->getHandoffs()
        );
        
        // Execute tools and side effects
        return $this->executeToolsAndSideEffects(
            $agent,
            $originalInput,
            $generatedItems,
            $response,
            $processedResponse,
            $contextWrapper,
            $runConfig
        );
    }
    
    /**
     * Run input guardrails.
     *
     * @param Agent $agent
     * @param array $guardrails
     * @param string|array $input
     * @param RunContext $context
     * @return array
     */
    protected function runInputGuardrails(
        Agent $agent,
        array $guardrails,
        $input,
        RunContext $context
    ): array {
        if (empty($guardrails)) {
            return [];
        }
        
        $results = [];
        
        foreach ($guardrails as $guardrail) {
            $result = $guardrail->check($input, $context, $agent);
            $results[] = new InputGuardrailResult($guardrail, $result);
            
            if ($result->tripwireTriggered) {
                throw new Exceptions\InputGuardrailTripwireTriggered($result);
            }
        }
        
        return $results;
    }
    
    /**
     * Run output guardrails.
     *
     * @param array $guardrails
     * @param Agent $agent
     * @param mixed $agentOutput
     * @param RunContext $context
     * @return array
     */
    protected function runOutputGuardrails(
        array $guardrails,
        Agent $agent,
        $agentOutput,
        RunContext $context
    ): array {
        if (empty($guardrails)) {
            return [];
        }
        
        $results = [];
        
        foreach ($guardrails as $guardrail) {
            $result = $guardrail->check($agentOutput, $context, $agent);
            $results[] = new OutputGuardrailResult($guardrail, $result);
            
            if ($result->tripwireTriggered) {
                throw new Exceptions\OutputGuardrailTripwireTriggered($result);
            }
        }
        
        return $results;
    }
    
    /**
     * Get the model to use for this agent.
     *
     * @param Agent $agent
     * @param RunConfig $runConfig
     * @return Models\Model
     */
    protected function getModel(Agent $agent, RunConfig $runConfig): Models\Model
    {
        if ($runConfig->model instanceof Models\Model) {
            return $runConfig->model;
        } elseif (is_string($runConfig->model)) {
            return $this->modelProvider->getModel($runConfig->model);
        } elseif ($agent->getModel() instanceof Models\Model) {
            return $agent->getModel();
        }
        
        return $this->modelProvider->getModel($agent->getModel() ?? config('agents.default_model'));
    }
    
    /**
     * Resolve model settings.
     *
     * @param ModelSettings $agentSettings
     * @param ModelSettings|null $runConfigSettings
     * @return ModelSettings
     */
    protected function resolveModelSettings(ModelSettings $agentSettings, ?ModelSettings $runConfigSettings): ModelSettings
    {
        if ($runConfigSettings === null) {
            return $agentSettings;
        }
        
        return $agentSettings->merge($runConfigSettings);
    }
    
    /**
     * Process the model response.
     *
     * @param Agent $agent
     * @param ModelResponse $response
     * @param string|null $outputType
     * @param array $handoffs
     * @return array
     */
    protected function processModelResponse(
        Agent $agent,
        ModelResponse $response,
        ?string $outputType,
        array $handoffs
    ): array {
        // Process the response to determine if it's a final output, handoff, or tool call
        $output = $response->getOutput();
        
        // Check for handoffs
        foreach ($handoffs as $handoff) {
            if ($handoff instanceof Handoff && isset($output['handoff']) && $output['handoff'] === $handoff->getAgentName()) {
                return [
                    'type' => 'handoff',
                    'handoff' => $handoff,
                    'newAgent' => $handoff->getAgent(),
                ];
            }
        }
        
        // Check for tool calls
        if (isset($output['tool_calls']) && !empty($output['tool_calls'])) {
            return [
                'type' => 'tool_calls',
                'toolCalls' => $output['tool_calls'],
            ];
        }
        
        // If no handoffs or tool calls, it's a final output
        return [
            'type' => 'final_output',
            'output' => $outputType ? $output : $output['content'] ?? '',
        ];
    }
    
    /**
     * Execute tools and side effects.
     *
     * @param Agent $agent
     * @param string|array $originalInput
     * @param array $preStepItems
     * @param ModelResponse $newResponse
     * @param array $processedResponse
     * @param RunContext $contextWrapper
     * @param RunConfig $runConfig
     * @return array
     */
    protected function executeToolsAndSideEffects(
        Agent $agent,
        $originalInput,
        array $preStepItems,
        ModelResponse $newResponse,
        array $processedResponse,
        RunContext $contextWrapper,
        RunConfig $runConfig
    ): array {
        $generatedItems = $preStepItems;
        $nextStep = null;
        
        // Add the AI response as an item
        $generatedItems[] = new RunItem('ai_message', $newResponse->getOutput());
        
        if ($processedResponse['type'] === 'final_output') {
            $nextStep = [
                'type' => 'final_output',
                'output' => $processedResponse['output'],
            ];
        } elseif ($processedResponse['type'] === 'handoff') {
            $nextStep = [
                'type' => 'handoff',
                'newAgent' => $processedResponse['newAgent'],
            ];
        } elseif ($processedResponse['type'] === 'tool_calls') {
            // Execute tool calls
            foreach ($processedResponse['toolCalls'] as $toolCall) {
                $tool = $this->findTool($agent->getTools(), $toolCall['name']);
                
                if ($tool) {
                    $arguments = $toolCall['arguments'] ?? [];
                    $result = $tool->execute($contextWrapper, $arguments);
                    
                    $generatedItems[] = new RunItem('tool_result', [
                        'tool_name' => $toolCall['name'],
                        'result' => $result,
                    ]);
                }
            }
            
            $nextStep = [
                'type' => 'run_again',
            ];
        }
        
        return [
            'originalInput' => $originalInput,
            'generatedItems' => $generatedItems,
            'modelResponse' => $newResponse,
            'nextStep' => $nextStep,
        ];
    }
    
    /**
     * Find a tool by name.
     *
     * @param array $tools
     * @param string $name
     * @return mixed|null
     */
    protected function findTool(array $tools, string $name)
    {
        foreach ($tools as $tool) {
            if ($tool->getName() === $name) {
                return $tool;
            }
        }
        
        return null;
    }
    
    /**
     * Prepare input for the model.
     *
     * @param string|array $originalInput
     * @param array $generatedItems
     * @return array
     */
    protected function prepareInput($originalInput, array $generatedItems): array
    {
        $input = is_string($originalInput) ? [['role' => 'user', 'content' => $originalInput]] : $originalInput;
        
        foreach ($generatedItems as $item) {
            $input[] = $item->toInputItem();
        }
        
        return $input;
    }
    
    /**
     * Get handoff names.
     *
     * @param Agent $agent
     * @return array
     */
    protected function getHandoffNames(Agent $agent): array
    {
        $names = [];
        foreach ($agent->getHandoffs() as $handoff) {
            if ($handoff instanceof Handoff) {
                $names[] = $handoff->getAgentName();
            } elseif ($handoff instanceof Agent) {
                $names[] = $handoff->getName();
            }
        }
        return $names;
    }
    
    /**
     * Get tool names.
     *
     * @param Agent $agent
     * @return array
     */
    protected function getToolNames(Agent $agent): array
    {
        $names = [];
        foreach ($agent->getTools() as $tool) {
            $names[] = $tool->getName();
        }
        return $names;
    }
}