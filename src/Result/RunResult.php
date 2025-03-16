<?php

namespace OpenAI\Agents\Result;

use OpenAI\Agents\Agent;
use OpenAI\Agents\Items\ModelResponse;
use OpenAI\Agents\Items\RunItem;
use OpenAI\Agents\Items\ItemHelpers;
use OpenAI\Agents\Guardrails\InputGuardrailResult;
use OpenAI\Agents\Guardrails\OutputGuardrailResult;

class RunResult
{
    /**
     * The original input to the agent.
     *
     * @var mixed
     */
    protected $input;
    
    /**
     * The items generated during the run.
     *
     * @var array
     */
    protected array $newItems;
    
    /**
     * The raw responses from the model.
     *
     * @var array
     */
    protected array $rawResponses;
    
    /**
     * The final output of the run.
     *
     * @var mixed
     */
    protected $finalOutput;
    
    /**
     * The last agent that ran.
     *
     * @var Agent
     */
    protected Agent $lastAgent;
    
    /**
     * The input guardrail results.
     *
     * @var array
     */
    protected array $inputGuardrailResults;
    
    /**
     * The output guardrail results.
     *
     * @var array
     */
    protected array $outputGuardrailResults;
    
    /**
     * Create a new run result instance.
     *
     * @param mixed $input
     * @param array $newItems
     * @param array $rawResponses
     * @param mixed $finalOutput
     * @param Agent $lastAgent
     * @param array $inputGuardrailResults
     * @param array $outputGuardrailResults
     */
    public function __construct(
        $input,
        array $newItems,
        array $rawResponses,
        $finalOutput,
        Agent $lastAgent,
        array $inputGuardrailResults = [],
        array $outputGuardrailResults = []
    ) {
        $this->input = $input;
        $this->newItems = $newItems;
        $this->rawResponses = $rawResponses;
        $this->finalOutput = $finalOutput;
        $this->lastAgent = $lastAgent;
        $this->inputGuardrailResults = $inputGuardrailResults;
        $this->outputGuardrailResults = $outputGuardrailResults;
    }
    
    /**
     * Get the original input to the agent.
     *
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }
    
    /**
     * Get the items generated during the run.
     *
     * @return array
     */
    public function getNewItems(): array
    {
        return $this->newItems;
    }
    
    /**
     * Get the raw responses from the model.
     *
     * @return array
     */
    public function getRawResponses(): array
    {
        return $this->rawResponses;
    }
    
    /**
     * Get the final output of the run.
     *
     * @return mixed
     */
    public function getFinalOutput()
    {
        return $this->finalOutput;
    }
    
    /**
     * Get the last agent that ran.
     *
     * @return Agent
     */
    public function getLastAgent(): Agent
    {
        return $this->lastAgent;
    }
    
    /**
     * Get the input guardrail results.
     *
     * @return array
     */
    public function getInputGuardrailResults(): array
    {
        return $this->inputGuardrailResults;
    }
    
    /**
     * Get the output guardrail results.
     *
     * @return array
     */
    public function getOutputGuardrailResults(): array
    {
        return $this->outputGuardrailResults;
    }
    
    /**
     * Get the text output.
     *
     * @return string
     */
    public function getTextOutput(): string
    {
        if (is_string($this->finalOutput)) {
            return $this->finalOutput;
        }
        
        return ItemHelpers::textMessageOutputs($this->newItems);
    }
}