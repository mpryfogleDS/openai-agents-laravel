<?php

namespace OpenAI\Agents\Tracing;

use Illuminate\Support\Facades\Log;

class LogProcessor implements TraceProcessorInterface
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
    public function onTraceStart(string $traceId, string $workflowName, ?string $groupId, ?array $metadata): void
    {
        Log::debug("Trace started: {$workflowName}", [
            'trace_id' => $traceId,
            'group_id' => $groupId,
            'metadata' => $metadata,
        ]);
    }
    
    /**
     * Called when a span starts.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @return void
     */
    public function onSpanStart(string $traceId, TraceSpan $span): void
    {
        Log::debug("Span started: {$span->getName()}", [
            'trace_id' => $traceId,
            'span_id' => $span->getId(),
            'type' => $span->getType(),
            'attributes' => $span->getAttributes(),
        ]);
    }
    
    /**
     * Called when a span has an error.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @param string $message
     * @param array $data
     * @return void
     */
    public function onSpanError(string $traceId, TraceSpan $span, string $message, array $data): void
    {
        Log::error("Span error: {$message}", [
            'trace_id' => $traceId,
            'span_id' => $span->getId(),
            'span_name' => $span->getName(),
            'data' => $data,
        ]);
    }
    
    /**
     * Called when a span ends.
     *
     * @param string $traceId
     * @param TraceSpan $span
     * @return void
     */
    public function onSpanEnd(string $traceId, TraceSpan $span): void
    {
        Log::debug("Span ended: {$span->getName()}", [
            'trace_id' => $traceId,
            'span_id' => $span->getId(),
            'duration' => $span->getDuration(),
            'errors' => $span->getErrors(),
        ]);
    }
    
    /**
     * Called when a trace ends.
     *
     * @param string $traceId
     * @param array $spans
     * @param int $duration
     * @return void
     */
    public function onTraceEnd(string $traceId, array $spans, int $duration): void
    {
        Log::debug("Trace ended", [
            'trace_id' => $traceId,
            'duration' => $duration,
            'span_count' => count($spans),
        ]);
    }
}