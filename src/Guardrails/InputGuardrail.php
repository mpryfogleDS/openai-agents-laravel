<?php

namespace JawadAshraf\OpenAI\Agents\Guardrails;

use OpenAI\Agents\Agent;
use OpenAI\Agents\RunContext;

abstract class InputGuardrail
{
    /**
     * Get the name of the guardrail.
     *
     * @return string
     */
    public function getName(): string
    {
        return class_basename($this);
    }
    
    /**
     * Check if the input passes the guardrail.
     *
     * @param mixed $input
     * @param RunContext $context
     * @param Agent $agent
     * @return GuardrailOutput
     */
    abstract public function check($input, RunContext $context, Agent $agent): GuardrailOutput;
}