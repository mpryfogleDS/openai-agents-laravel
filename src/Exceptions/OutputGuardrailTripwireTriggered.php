<?php

namespace JawadAshraf\OpenAI\Agents\Exceptions;

use OpenAI\Agents\Guardrails\OutputGuardrailResult;

class OutputGuardrailTripwireTriggered extends AgentsException
{
    /**
     * The guardrail result.
     *
     * @var OutputGuardrailResult
     */
    protected OutputGuardrailResult $result;
    
    /**
     * Create a new exception instance.
     *
     * @param OutputGuardrailResult $result
     */
    public function __construct(OutputGuardrailResult $result)
    {
        $this->result = $result;
        parent::__construct("Output guardrail tripwire triggered: {$result->guardrail->getName()}");
    }
    
    /**
     * Get the guardrail result.
     *
     * @return OutputGuardrailResult
     */
    public function getResult(): OutputGuardrailResult
    {
        return $this->result;
    }
}