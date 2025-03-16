<?php

namespace OpenAI\Agents\Handoffs;

use OpenAI\Agents\Agent;
use OpenAI\Agents\RunContext;

class Handoff
{
    /**
     * The agent to hand off to.
     *
     * @var Agent
     */
    protected Agent $agent;
    
    /**
     * The name of the agent.
     *
     * @var string
     */
    protected string $agentName;
    
    /**
     * The description of the agent.
     *
     * @var string
     */
    protected string $description;
    
    /**
     * The input filter to apply to the conversation before handing off.
     *
     * @var HandoffInputFilter|null
     */
    protected ?HandoffInputFilter $inputFilter = null;
    
    /**
     * Create a new handoff instance.
     *
     * @param Agent $agent
     * @param string|null $description
     * @param HandoffInputFilter|null $inputFilter
     */
    public function __construct(Agent $agent, ?string $description = null, ?HandoffInputFilter $inputFilter = null)
    {
        $this->agent = $agent;
        $this->agentName = $agent->getName();
        $this->description = $description ?? $agent->getHandoffDescription() ?? "Handoff to {$agent->getName()} agent";
        $this->inputFilter = $inputFilter;
    }
    
    /**
     * Get the agent.
     *
     * @return Agent
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }
    
    /**
     * Get the agent name.
     *
     * @return string
     */
    public function getAgentName(): string
    {
        return $this->agentName;
    }
    
    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Get the input filter.
     *
     * @return HandoffInputFilter|null
     */
    public function getInputFilter(): ?HandoffInputFilter
    {
        return $this->inputFilter;
    }
    
    /**
     * Set the input filter.
     *
     * @param HandoffInputFilter $inputFilter
     * @return $this
     */
    public function withInputFilter(HandoffInputFilter $inputFilter): self
    {
        $clone = clone $this;
        $clone->inputFilter = $inputFilter;
        return $clone;
    }
    
    /**
     * Apply the input filter.
     *
     * @param array $input
     * @param RunContext $context
     * @return array
     */
    public function applyInputFilter(array $input, RunContext $context): array
    {
        if (!$this->inputFilter) {
            return $input;
        }
        
        return $this->inputFilter->filter($input, $context, $this);
    }
}