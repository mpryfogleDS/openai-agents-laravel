<?php

namespace OpenAI\Agents;

use OpenAI\Agents\Items\Usage;

class RunContext
{
    /**
     * The context object.
     *
     * @var mixed
     */
    protected mixed $context;
    
    /**
     * The usage stats.
     *
     * @var Usage
     */
    protected Usage $usage;
    
    /**
     * Create a new run context instance.
     *
     * @param mixed|null $context
     */
    public function __construct(mixed $context = null)
    {
        $this->context = $context;
        $this->usage = new Usage();
    }
    
    /**
     * Get the context.
     *
     * @return mixed
     */
    public function getContext(): mixed
    {
        return $this->context;
    }
    
    /**
     * Set the context.
     *
     * @param mixed $context
     * @return $this
     */
    public function setContext(mixed $context): self
    {
        $this->context = $context;
        return $this;
    }
    
    /**
     * Get the usage stats.
     *
     * @return Usage
     */
    public function getUsage(): Usage
    {
        return $this->usage;
    }
    
    /**
     * Add usage stats.
     *
     * @param Usage $usage
     * @return $this
     */
    public function addUsage(Usage $usage): self
    {
        $this->usage->add($usage);
        return $this;
    }
}