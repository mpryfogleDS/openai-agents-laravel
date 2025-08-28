<?php

namespace OpenAI\Agents\Exceptions;

use OpenAI\Agents\Guardrails\InputGuardrailResult;

class InputGuardrailTripwireTriggered extends AgentsException
{
    /**
     * The guardrail result.
     *
     * @var InputGuardrailResult
     */
    protected InputGuardrailResult $result;
    
    /**
     * Create a new exception instance.
     *
     * @param InputGuardrailResult $result
     */
    public function __construct(InputGuardrailResult $result)
    {
        $this->result = $result;
        parent::__construct("Input guardrail tripwire triggered: {$result->guardrail->getName()}");
    }
    
    /**
     * Get the guardrail result.
     *
     * @return InputGuardrailResult
     */
    public function getResult(): InputGuardrailResult
    {
        return $this->result;
    }
}