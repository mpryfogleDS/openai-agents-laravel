<?php

namespace OpenAI\Agents\Models;

class ModelSettings
{
    /**
     * The temperature parameter.
     *
     * @var float|null
     */
    protected ?float $temperature;
    
    /**
     * The top_p parameter.
     *
     * @var float|null
     */
    protected ?float $topP;
    
    /**
     * The frequency_penalty parameter.
     *
     * @var float|null
     */
    protected ?float $frequencyPenalty;
    
    /**
     * The presence_penalty parameter.
     *
     * @var float|null
     */
    protected ?float $presencePenalty;
    
    /**
     * The max_tokens parameter.
     *
     * @var int|null
     */
    protected ?int $maxTokens;
    
    /**
     * The timeout in seconds.
     *
     * @var int|null
     */
    protected ?int $timeout;
    
    /**
     * Create a new model settings instance.
     *
     * @param float|null $temperature
     * @param float|null $topP
     * @param float|null $frequencyPenalty
     * @param float|null $presencePenalty
     * @param int|null $maxTokens
     * @param int|null $timeout
     */
    public function __construct(
        ?float $temperature = null,
        ?float $topP = null,
        ?float $frequencyPenalty = null,
        ?float $presencePenalty = null,
        ?int $maxTokens = null,
        ?int $timeout = null
    ) {
        $this->temperature = $temperature;
        $this->topP = $topP;
        $this->frequencyPenalty = $frequencyPenalty;
        $this->presencePenalty = $presencePenalty;
        $this->maxTokens = $maxTokens;
        $this->timeout = $timeout;
    }
    
    /**
     * Create a new instance with default values from config.
     *
     * @return static
     */
    public static function default(): self
    {
        return new self(
            config('agents.model_settings.temperature'),
            config('agents.model_settings.top_p'),
            config('agents.model_settings.frequency_penalty'),
            config('agents.model_settings.presence_penalty'),
            config('agents.model_settings.max_tokens'),
            config('agents.model_settings.timeout')
        );
    }
    
    /**
     * Create a new instance with the given temperature.
     *
     * @param float $temperature
     * @return static
     */
    public function withTemperature(float $temperature): self
    {
        $clone = clone $this;
        $clone->temperature = $temperature;
        return $clone;
    }
    
    /**
     * Create a new instance with the given top_p.
     *
     * @param float $topP
     * @return static
     */
    public function withTopP(float $topP): self
    {
        $clone = clone $this;
        $clone->topP = $topP;
        return $clone;
    }
    
    /**
     * Create a new instance with the given frequency_penalty.
     *
     * @param float $frequencyPenalty
     * @return static
     */
    public function withFrequencyPenalty(float $frequencyPenalty): self
    {
        $clone = clone $this;
        $clone->frequencyPenalty = $frequencyPenalty;
        return $clone;
    }
    
    /**
     * Create a new instance with the given presence_penalty.
     *
     * @param float $presencePenalty
     * @return static
     */
    public function withPresencePenalty(float $presencePenalty): self
    {
        $clone = clone $this;
        $clone->presencePenalty = $presencePenalty;
        return $clone;
    }
    
    /**
     * Create a new instance with the given max_tokens.
     *
     * @param int $maxTokens
     * @return static
     */
    public function withMaxTokens(int $maxTokens): self
    {
        $clone = clone $this;
        $clone->maxTokens = $maxTokens;
        return $clone;
    }
    
    /**
     * Create a new instance with the given timeout.
     *
     * @param int $timeout
     * @return static
     */
    public function withTimeout(int $timeout): self
    {
        $clone = clone $this;
        $clone->timeout = $timeout;
        return $clone;
    }
    
    /**
     * Merge this settings with another settings object.
     *
     * @param ModelSettings|null $other
     * @return ModelSettings
     */
    public function merge(?ModelSettings $other): ModelSettings
    {
        if (!$other) {
            return $this;
        }
        
        return new ModelSettings(
            $other->temperature ?? $this->temperature,
            $other->topP ?? $this->topP,
            $other->frequencyPenalty ?? $this->frequencyPenalty,
            $other->presencePenalty ?? $this->presencePenalty,
            $other->maxTokens ?? $this->maxTokens,
            $other->timeout ?? $this->timeout
        );
    }
    
    /**
     * Resolve this settings with another settings object.
     *
     * @param ModelSettings|null $other
     * @return ModelSettings
     */
    public function resolve(?ModelSettings $other): ModelSettings
    {
        if (!$other) {
            return $this;
        }
        
        $result = clone $this;
        
        if ($other->temperature !== null) {
            $result->temperature = $other->temperature;
        }
        
        if ($other->topP !== null) {
            $result->topP = $other->topP;
        }
        
        if ($other->frequencyPenalty !== null) {
            $result->frequencyPenalty = $other->frequencyPenalty;
        }
        
        if ($other->presencePenalty !== null) {
            $result->presencePenalty = $other->presencePenalty;
        }
        
        if ($other->maxTokens !== null) {
            $result->maxTokens = $other->maxTokens;
        }
        
        if ($other->timeout !== null) {
            $result->timeout = $other->timeout;
        }
        
        return $result;
    }
    
    /**
     * Get the temperature parameter.
     *
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }
    
    /**
     * Get the top_p parameter.
     *
     * @return float|null
     */
    public function getTopP(): ?float
    {
        return $this->topP;
    }
    
    /**
     * Get the frequency_penalty parameter.
     *
     * @return float|null
     */
    public function getFrequencyPenalty(): ?float
    {
        return $this->frequencyPenalty;
    }
    
    /**
     * Get the presence_penalty parameter.
     *
     * @return float|null
     */
    public function getPresencePenalty(): ?float
    {
        return $this->presencePenalty;
    }
    
    /**
     * Get the max_tokens parameter.
     *
     * @return int|null
     */
    public function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }
    
    /**
     * Get the timeout in seconds.
     *
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }
    
    /**
     * Convert the settings to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        
        if ($this->temperature !== null) {
            $result['temperature'] = $this->temperature;
        }
        
        if ($this->topP !== null) {
            $result['top_p'] = $this->topP;
        }
        
        if ($this->frequencyPenalty !== null) {
            $result['frequency_penalty'] = $this->frequencyPenalty;
        }
        
        if ($this->presencePenalty !== null) {
            $result['presence_penalty'] = $this->presencePenalty;
        }
        
        if ($this->maxTokens !== null) {
            $result['max_tokens'] = $this->maxTokens;
        }
        
        return $result;
    }
}