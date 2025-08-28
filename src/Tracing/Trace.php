<?php

namespace OpenAI\Agents\Tracing;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Trace
{
    /**
     * Whether tracing is disabled.
     *
     * @var bool
     */
    protected bool $disabled = false;
    
    /**
     * The trace ID.
     *
     * @var string|null
     */
    protected ?string $traceId = null;
    
    /**
     * The group ID.
     *
     * @var string|null
     */
    protected ?string $groupId = null;
    
    /**
     * The workflow name.
     *
     * @var string|null
     */
    protected ?string $workflowName = null;
    
    /**
     * Additional metadata.
     *
     * @var array|null
     */
    protected ?array $metadata = null;
    
    /**
     * The start time.
     *
     * @var int|null
     */
    protected ?int $startTime = null;
    
    /**
     * The end time.
     *
     * @var int|null
     */
    protected ?int $endTime = null;
    
    /**
     * The current spans.
     *
     * @var array
     */
    protected array $spans = [];
    
    /**
     * The current span.
     *
     * @var TraceSpan|null
     */
    protected ?TraceSpan $currentSpan = null;
    
    /**
     * The trace processors.
     *
     * @var array
     */
    protected array $processors = [];
    
    /**
     * Create a new trace instance.
     */
    public function __construct()
    {
        $this->processors = $this->getDefaultProcessors();
    }
    
    /**
     * Get the default trace processors.
     *
     * @return array
     */
    protected function getDefaultProcessors(): array
    {
        return [
            new LogProcessor(),
        ];
    }
    
    /**
     * Start a new trace.
     *
     * @param string $workflowName
     * @param string|null $traceId
     * @param string|null $groupId
     * @param array|null $metadata
     * @return $this
     */
    public function start(string $workflowName, ?string $traceId = null, ?string $groupId = null, ?array $metadata = null): self
    {
        if ($this->disabled) {
            return $this;
        }
        
        $this->workflowName = $workflowName;
        $this->traceId = $traceId ?? (string) Str::uuid();
        $this->groupId = $groupId;
        $this->metadata = $metadata;
        $this->startTime = $this->getCurrentTimeMicro();
        
        foreach ($this->processors as $processor) {
            $processor->onTraceStart($this->traceId, $this->workflowName, $this->groupId, $this->metadata);
        }
        
        return $this;
    }
    
    /**
     * Start a new agent span.
     *
     * @param string $name
     * @param array $handoffs
     * @param array $tools
     * @param string $outputType
     * @return string
     */
    public function startAgentSpan(string $name, array $handoffs, array $tools, string $outputType): string
    {
        if ($this->disabled) {
            return '';
        }
        
        $spanId = (string) Str::uuid();
        
        $span = new TraceSpan(
            $spanId,
            $name,
            'agent',
            $this->getCurrentTimeMicro(),
            [
                'handoffs' => $handoffs,
                'tools' => $tools,
                'output_type' => $outputType,
            ]
        );
        
        $this->spans[$spanId] = $span;
        $this->currentSpan = $span;
        
        foreach ($this->processors as $processor) {
            $processor->onSpanStart($this->traceId, $span);
        }
        
        return $spanId;
    }
    
    /**
     * Add an error to a span.
     *
     * @param string $spanId
     * @param string $message
     * @param array $data
     * @return $this
     */
    public function addErrorToSpan(string $spanId, string $message, array $data = []): self
    {
        if ($this->disabled || !isset($this->spans[$spanId])) {
            return $this;
        }
        
        $span = $this->spans[$spanId];
        $span->addError($message, $data);
        
        foreach ($this->processors as $processor) {
            $processor->onSpanError($this->traceId, $span, $message, $data);
        }
        
        return $this;
    }
    
    /**
     * Finish a span.
     *
     * @param string $spanId
     * @return $this
     */
    public function finishSpan(string $spanId): self
    {
        if ($this->disabled || !isset($this->spans[$spanId])) {
            return $this;
        }
        
        $span = $this->spans[$spanId];
        $span->finish($this->getCurrentTimeMicro());
        
        foreach ($this->processors as $processor) {
            $processor->onSpanEnd($this->traceId, $span);
        }
        
        $this->currentSpan = null;
        
        return $this;
    }
    
    /**
     * Finish the trace.
     *
     * @return $this
     */
    public function finish(): self
    {
        if ($this->disabled) {
            return $this;
        }
        
        $this->endTime = $this->getCurrentTimeMicro();
        
        foreach ($this->processors as $processor) {
            $processor->onTraceEnd($this->traceId, $this->spans, $this->endTime - $this->startTime);
        }
        
        return $this;
    }
    
    /**
     * Set whether tracing is disabled.
     *
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }
    
    /**
     * Get the current time in microseconds.
     *
     * @return int
     */
    protected function getCurrentTimeMicro(): int
    {
        return (int) (microtime(true) * 1000000);
    }
}