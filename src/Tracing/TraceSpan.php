<?php

namespace OpenAI\Agents\Tracing;

class TraceSpan
{
    /**
     * The span ID.
     *
     * @var string
     */
    protected string $id;
    
    /**
     * The span name.
     *
     * @var string
     */
    protected string $name;
    
    /**
     * The span type.
     *
     * @var string
     */
    protected string $type;
    
    /**
     * The start time.
     *
     * @var int
     */
    protected int $startTime;
    
    /**
     * The end time.
     *
     * @var int|null
     */
    protected ?int $endTime = null;
    
    /**
     * The span attributes.
     *
     * @var array
     */
    protected array $attributes;
    
    /**
     * The span errors.
     *
     * @var array
     */
    protected array $errors = [];
    
    /**
     * Create a new trace span instance.
     *
     * @param string $id
     * @param string $name
     * @param string $type
     * @param int $startTime
     * @param array $attributes
     */
    public function __construct(string $id, string $name, string $type, int $startTime, array $attributes = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->startTime = $startTime;
        $this->attributes = $attributes;
    }
    
    /**
     * Add an error to the span.
     *
     * @param string $message
     * @param array $data
     * @return $this
     */
    public function addError(string $message, array $data = []): self
    {
        $this->errors[] = [
            'message' => $message,
            'data' => $data,
        ];
        
        return $this;
    }
    
    /**
     * Finish the span.
     *
     * @param int $endTime
     * @return $this
     */
    public function finish(int $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }
    
    /**
     * Get the span ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * Get the span name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Get the span type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * Get the start time.
     *
     * @return int
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }
    
    /**
     * Get the end time.
     *
     * @return int|null
     */
    public function getEndTime(): ?int
    {
        return $this->endTime;
    }
    
    /**
     * Get the span attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Get the span errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get the span duration in microseconds.
     *
     * @return int|null
     */
    public function getDuration(): ?int
    {
        if ($this->endTime === null) {
            return null;
        }
        
        return $this->endTime - $this->startTime;
    }
}