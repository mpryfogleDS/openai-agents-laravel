<?php

namespace OpenAI\Agents\Items;

class Usage
{
    /**
     * The number of requests made.
     *
     * @var int
     */
    protected int $requests = 0;
    
    /**
     * The number of input tokens used.
     *
     * @var int
     */
    protected int $inputTokens = 0;
    
    /**
     * The number of output tokens used.
     *
     * @var int
     */
    protected int $outputTokens = 0;
    
    /**
     * The total number of tokens used.
     *
     * @var int
     */
    protected int $totalTokens = 0;
    
    /**
     * Create a new usage instance.
     *
     * @param int $requests
     * @param int $inputTokens
     * @param int $outputTokens
     * @param int $totalTokens
     */
    public function __construct(
        int $requests = 0,
        int $inputTokens = 0,
        int $outputTokens = 0,
        int $totalTokens = 0
    ) {
        $this->requests = $requests;
        $this->inputTokens = $inputTokens;
        $this->outputTokens = $outputTokens;
        $this->totalTokens = $totalTokens ?: ($inputTokens + $outputTokens);
    }
    
    /**
     * Add another usage to this one.
     *
     * @param Usage $other
     * @return $this
     */
    public function add(Usage $other): self
    {
        $this->requests += $other->requests;
        $this->inputTokens += $other->inputTokens;
        $this->outputTokens += $other->outputTokens;
        $this->totalTokens += $other->totalTokens;
        
        return $this;
    }
    
    /**
     * Get the number of requests made.
     *
     * @return int
     */
    public function getRequests(): int
    {
        return $this->requests;
    }
    
    /**
     * Get the number of input tokens used.
     *
     * @return int
     */
    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }
    
    /**
     * Get the number of output tokens used.
     *
     * @return int
     */
    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }
    
    /**
     * Get the total number of tokens used.
     *
     * @return int
     */
    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }
}