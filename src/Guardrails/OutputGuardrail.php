<?php

namespace OpenAI\Agents\Guardrails;

use OpenAI\Agents\Agent;
use OpenAI\Agents\RunContext;

abstract class OutputGuardrail
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
     * Check if the output passes the guardrail.
     *
     * @param mixed $output
     * @param RunContext $context
     * @param Agent $agent
     * @return GuardrailOutput
     */
    abstract public function check($output, RunContext $context, Agent $agent): GuardrailOutput;
}