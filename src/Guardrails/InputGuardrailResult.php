<?php

namespace JawadAshraf\OpenAI\Agents\Guardrails;

class InputGuardrailResult
{
    /**
     * The guardrail that produced this result.
     *
     * @var InputGuardrail
     */
    public InputGuardrail $guardrail;
    
    /**
     * The output from the guardrail.
     *
     * @var GuardrailOutput
     */
    public GuardrailOutput $output;
    
    /**
     * Create a new input guardrail result.
     *
     * @param InputGuardrail $guardrail
     * @param GuardrailOutput $output
     */
    public function __construct(InputGuardrail $guardrail, GuardrailOutput $output)
    {
        $this->guardrail = $guardrail;
        $this->output = $output;
    }
}