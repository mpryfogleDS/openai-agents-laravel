<?php

namespace JawadAshraf\OpenAI\Agents\Guardrails;

class GuardrailOutput
{
    /**
     * Whether the guardrail tripwire was triggered.
     *
     * @var bool
     */
    public bool $tripwireTriggered = false;
    
    /**
     * The message from the guardrail.
     *
     * @var string|null
     */
    public ?string $message = null;
    
    /**
     * Create a new guardrail output.
     *
     * @param bool $tripwireTriggered
     * @param string|null $message
     */
    public function __construct(bool $tripwireTriggered = false, ?string $message = null)
    {
        $this->tripwireTriggered = $tripwireTriggered;
        $this->message = $message;
    }
    
    /**
     * Create a new instance for a passed guardrail.
     *
     * @param string|null $message
     * @return static
     */
    public static function passed(?string $message = null): self
    {
        return new self(false, $message);
    }
    
    /**
     * Create a new instance for a failed guardrail.
     *
     * @param string $message
     * @return static
     */
    public static function failed(string $message): self
    {
        return new self(true, $message);
    }
}