<?php

namespace OpenAI\Agents;

use OpenAI\Agents\Models\Model;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Models\ModelProviderInterface;
use OpenAI\Agents\Models\OpenAIProvider;
use OpenAI\Agents\Handoffs\HandoffInputFilter;
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\OutputGuardrail;

class RunConfig
{
    /**
     * The model to use for the entire agent run.
     *
     * @var string|Model|null
     */
    public $model = null;
    
    /**
     * The model provider to use when looking up string model names.
     *
     * @var ModelProviderInterface
     */
    public $modelProvider;
    
    /**
     * Configure global model settings.
     *
     * @var ModelSettings|null
     */
    public $modelSettings = null;
    
    /**
     * A global input filter to apply to all handoffs.
     *
     * @var HandoffInputFilter|null
     */
    public $handoffInputFilter = null;
    
    /**
     * A list of input guardrails to run on the initial run input.
     *
     * @var array
     */
    public $inputGuardrails = [];
    
    /**
     * A list of output guardrails to run on the final output of the run.
     *
     * @var array
     */
    public $outputGuardrails = [];
    
    /**
     * Whether tracing is disabled for the agent run.
     *
     * @var bool
     */
    public $tracingDisabled = false;
    
    /**
     * Whether we include potentially sensitive data in traces.
     *
     * @var bool
     */
    public $traceIncludeSensitiveData = true;
    
    /**
     * The name of the run, used for tracing.
     *
     * @var string
     */
    public $workflowName = 'Agent workflow';
    
    /**
     * A custom trace ID to use for tracing.
     *
     * @var string|null
     */
    public $traceId = null;
    
    /**
     * A grouping identifier to use for tracing.
     *
     * @var string|null
     */
    public $groupId = null;
    
    /**
     * An optional dictionary of additional metadata to include with the trace.
     *
     * @var array|null
     */
    public $traceMetadata = null;
    
    /**
     * Create a new run config instance.
     */
    public function __construct()
    {
        $this->modelProvider = app(ModelProviderInterface::class);
        $this->tracingDisabled = !config('agents.tracing.enabled', true);
        $this->traceIncludeSensitiveData = config('agents.tracing.include_sensitive_data', false);
    }
    
    /**
     * Set the model to use for the entire agent run.
     *
     * @param string|Model $model
     * @return $this
     */
    public function withModel($model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Set the model provider to use when looking up string model names.
     *
     * @param ModelProviderInterface $provider
     * @return $this
     */
    public function withModelProvider(ModelProviderInterface $provider): self
    {
        $this->modelProvider = $provider;
        return $this;
    }
    
    /**
     * Set the model settings.
     *
     * @param ModelSettings $settings
     * @return $this
     */
    public function withModelSettings(ModelSettings $settings): self
    {
        $this->modelSettings = $settings;
        return $this;
    }
    
    /**
     * Set the handoff input filter.
     *
     * @param HandoffInputFilter $filter
     * @return $this
     */
    public function withHandoffInputFilter(HandoffInputFilter $filter): self
    {
        $this->handoffInputFilter = $filter;
        return $this;
    }
    
    /**
     * Set the input guardrails.
     *
     * @param array $guardrails
     * @return $this
     */
    public function withInputGuardrails(array $guardrails): self
    {
        $this->inputGuardrails = $guardrails;
        return $this;
    }
    
    /**
     * Set the output guardrails.
     *
     * @param array $guardrails
     * @return $this
     */
    public function withOutputGuardrails(array $guardrails): self
    {
        $this->outputGuardrails = $guardrails;
        return $this;
    }
    
    /**
     * Set whether tracing is disabled.
     *
     * @param bool $disabled
     * @return $this
     */
    public function withTracingDisabled(bool $disabled = true): self
    {
        $this->tracingDisabled = $disabled;
        return $this;
    }
    
    /**
     * Set whether to include sensitive data in traces.
     *
     * @param bool $include
     * @return $this
     */
    public function withTraceIncludeSensitiveData(bool $include = true): self
    {
        $this->traceIncludeSensitiveData = $include;
        return $this;
    }
    
    /**
     * Set the workflow name.
     *
     * @param string $name
     * @return $this
     */
    public function withWorkflowName(string $name): self
    {
        $this->workflowName = $name;
        return $this;
    }
    
    /**
     * Set the trace ID.
     *
     * @param string $traceId
     * @return $this
     */
    public function withTraceId(string $traceId): self
    {
        $this->traceId = $traceId;
        return $this;
    }
    
    /**
     * Set the group ID.
     *
     * @param string $groupId
     * @return $this
     */
    public function withGroupId(string $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }
    
    /**
     * Set the trace metadata.
     *
     * @param array $metadata
     * @return $this
     */
    public function withTraceMetadata(array $metadata): self
    {
        $this->traceMetadata = $metadata;
        return $this;
    }
}