<?php

namespace OpenAI\Agents\Guardrails;

class OutputGuardrailResult
{
    /**
     * The guardrail that produced this result.
     *
     * @var OutputGuardrail
     */
    public OutputGuardrail $guardrail;
    
    /**
     * The output from the guardrail.
     *
     * @var GuardrailOutput
     */
    public GuardrailOutput $output;
    
    /**
     * Create a new output guardrail result.
     *
     * @param OutputGuardrail $guardrail
     * @param GuardrailOutput $output
     */
    public function __construct(OutputGuardrail $guardrail, GuardrailOutput $output)
    {
        $this->guardrail = $guardrail;
        $this->output = $output;
    }
}