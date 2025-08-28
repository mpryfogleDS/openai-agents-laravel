<?php

namespace OpenAI\Agents\Tracing;

interface TraceProcessorInterface
{
    /**
     * Called when a trace starts.
     *
     * @param string $traceId
     * @param string $workflowName
     * @param string|null $groupId
     * @param array|null $metadata
     * @return void
     */
    public function onTraceStart(string $traceId, string $workflowName, ?string $groupId, ?array $metadata): void;
    
    /**
     * Called when a span starts.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @return void
     */
    public function onSpanStart(string $traceId, TraceSpan $span): void;
    
    /**
     * Called when a span has an error.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @param string $message
     * @param array $data
     * @return void
     */
    public function onSpanError(string $traceId, TraceSpan $span, string $message, array $data): void;
    
    /**
     * Called when a span ends.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @return void
     */
    public function onSpanEnd(string $traceId, TraceSpan $span): void;
    
    /**
     * Called when a trace ends.
     *
     * @param string $traceId
     * @param array $spans
     * @param int $duration
     * @return void
     */
    public function onTraceEnd(string $traceId, array $spans, int $duration): void;
}