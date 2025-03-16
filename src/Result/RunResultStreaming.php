<?php

namespace OpenAI\Agents\Result;

use OpenAI\Agents\Agent;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Runner;

class RunResultStreaming
{
    /**
     * The original input to the agent.
     *
     * @var mixed
     */
    public $input;
    
    /**
     * The items generated during the run.
     *
     * @var array
     */
    public array $newItems;
    
    /**
     * The current agent.
     *
     * @var Agent
     */
    public Agent $currentAgent;
    
    /**
     * The raw responses from the model.
     *
     * @var array
     */
    public array $rawResponses;
    
    /**
     * The final output of the run.
     *
     * @var mixed
     */
    public $finalOutput;
    
    /**
     * Whether the run is complete.
     *
     * @var bool
     */
    public bool $isComplete;
    
    /**
     * The current turn.
     *
     * @var int
     */
    public int $currentTurn;
    
    /**
     * The maximum number of turns.
     *
     * @var int
     */
    public int $maxTurns;
    
    /**
     * The input guardrail results.
     *
     * @var array
     */
    public array $inputGuardrailResults;
    
    /**
     * The output guardrail results.
     *
     * @var array
     */
    public array $outputGuardrailResults;
    
    /**
     * The runner instance.
     *
     * @var Runner|null
     */
    protected ?Runner $runner = null;
    
    /**
     * The context wrapper.
     *
     * @var RunContext|null
     */
    protected ?RunContext $context = null;
    
    /**
     * The run config.
     *
     * @var RunConfig|null
     */
    protected ?RunConfig $runConfig = null;
    
    /**
     * The streaming state.
     *
     * @var array
     */
    protected array $streamingState = [
        'hasStarted' => false,
        'token' => '',
        'toolCalls' => [],
        'currentToolCall' => null,
    ];
    
    /**
     * Create a new streaming run result instance.
     *
     * @param mixed $input
     * @param array $newItems
     * @param Agent $currentAgent
     * @param array $rawResponses
     * @param mixed $finalOutput
     * @param bool $isComplete
     * @param int $currentTurn
     * @param int $maxTurns
     * @param array $inputGuardrailResults
     * @param array $outputGuardrailResults
     */
    public function __construct(
        $input,
        array $newItems,
        Agent $currentAgent,
        array $rawResponses,
        $finalOutput,
        bool $isComplete,
        int $currentTurn,
        int $maxTurns,
        array $inputGuardrailResults = [],
        array $outputGuardrailResults = []
    ) {
        $this->input = $input;
        $this->newItems = $newItems;
        $this->currentAgent = $currentAgent;
        $this->rawResponses = $rawResponses;
        $this->finalOutput = $finalOutput;
        $this->isComplete = $isComplete;
        $this->currentTurn = $currentTurn;
        $this->maxTurns = $maxTurns;
        $this->inputGuardrailResults = $inputGuardrailResults;
        $this->outputGuardrailResults = $outputGuardrailResults;
    }
    
    /**
     * Set the runner.
     *
     * @param Runner $runner
     * @return $this
     */
    public function setRunner(Runner $runner): self
    {
        $this->runner = $runner;
        return $this;
    }
    
    /**
     * Set the context.
     *
     * @param RunContext $context
     * @return $this
     */
    public function setContext(RunContext $context): self
    {
        $this->context = $context;
        return $this;
    }
    
    /**
     * Set the run config.
     *
     * @param RunConfig $runConfig
     * @return $this
     */
    public function setRunConfig(RunConfig $runConfig): self
    {
        $this->runConfig = $runConfig;
        return $this;
    }
    
    /**
     * Start streaming the agent events.
     *
     * @param callable $onToken
     * @param callable|null $onToolCall
     * @param callable|null $onToolCallResult
     * @param callable|null $onAgentChange
     * @param callable|null $onComplete
     * @return $this
     */
    public function stream(
        callable $onToken,
        ?callable $onToolCall = null,
        ?callable $onToolCallResult = null,
        ?callable $onAgentChange = null,
        ?callable $onComplete = null
    ): self {
        // This method would initialize the streaming process
        // Due to PHP's synchronous nature, we need to simulate streaming
        // by running the agent loop and triggering callbacks during processing
        
        // In a real implementation, this would start an asynchronous process
        // or use a streaming response in a Controller
        
        // For now, we'll assume this is the entry point for streaming
        // and will be implemented in a concrete application
        
        return $this;
    }
    
    /**
     * Wait for the streaming run to complete.
     *
     * @return RunResult
     */
    public function wait(): RunResult
    {
        // If the streaming hasn't completed yet, wait for it
        if (!$this->isComplete && $this->runner && $this->context && $this->runConfig) {
            // In a real implementation, this would wait for the streaming to complete
            // For simplicity, we'll just run the agent synchronously here
            $result = $this->runner->run(
                $this->currentAgent,
                $this->input,
                $this->context->getContext(),
                $this->maxTurns,
                $this->runConfig
            );
            
            // Update our state from the result
            $this->newItems = $result->getNewItems();
            $this->rawResponses = $result->getRawResponses();
            $this->finalOutput = $result->getFinalOutput();
            $this->inputGuardrailResults = $result->getInputGuardrailResults();
            $this->outputGuardrailResults = $result->getOutputGuardrailResults();
            $this->isComplete = true;
        }
        
        return new RunResult(
            $this->input,
            $this->newItems,
            $this->rawResponses,
            $this->finalOutput,
            $this->currentAgent,
            $this->inputGuardrailResults,
            $this->outputGuardrailResults
        );
    }
}