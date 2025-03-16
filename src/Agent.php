<?php

namespace OpenAI\Agents;

use Closure;
use OpenAI\Agents\Models\Model;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\OutputGuardrail;
use OpenAI\Agents\Result\RunResult;

class Agent
{
    /**
     * The name of the agent.
     *
     * @var string
     */
    protected string $name;

    /**
     * The instructions for the agent.
     *
     * @var string|Closure|null
     */
    protected $instructions = null;

    /**
     * A description of the agent.
     *
     * @var string|null
     */
    protected ?string $handoffDescription = null;

    /**
     * Handoffs are sub-agents that the agent can delegate to.
     *
     * @var array
     */
    protected array $handoffs = [];

    /**
     * The model to use for this agent.
     *
     * @var string|Model|null
     */
    protected $model = null;

    /**
     * Configures model-specific tuning parameters.
     *
     * @var ModelSettings
     */
    protected ModelSettings $modelSettings;

    /**
     * A list of tools that the agent can use.
     *
     * @var array
     */
    protected array $tools = [];

    /**
     * A list of checks that run before generating a response.
     *
     * @var array
     */
    protected array $inputGuardrails = [];

    /**
     * A list of checks that run on the final output.
     *
     * @var array
     */
    protected array $outputGuardrails = [];

    /**
     * The type of the output object.
     *
     * @var string|null
     */
    protected ?string $outputType = null;

    /**
     * Create a new agent instance.
     *
     * @param string $name
     * @param string|Closure|null $instructions
     * @param ModelSettings|null $modelSettings
     */
    public function __construct(
        string $name,
        $instructions = null,
        ?ModelSettings $modelSettings = null
    ) {
        $this->name = $name;
        $this->instructions = $instructions;
        $this->modelSettings = $modelSettings ?? app(ModelSettings::class);
    }

    /**
     * Set the handoff description.
     *
     * @param string $description
     * @return $this
     */
    public function withHandoffDescription(string $description): self
    {
        $this->handoffDescription = $description;
        return $this;
    }

    /**
     * Set the handoffs for this agent.
     *
     * @param array $handoffs
     * @return $this
     */
    public function withHandoffs(array $handoffs): self
    {
        $this->handoffs = $handoffs;
        return $this;
    }

    /**
     * Set the model for this agent.
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
     * Set the model settings for this agent.
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
     * Set the tools for this agent.
     *
     * @param array $tools
     * @return $this
     */
    public function withTools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    /**
     * Set the input guardrails for this agent.
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
     * Set the output guardrails for this agent.
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
     * Set the output type for this agent.
     *
     * @param string $type
     * @return $this
     */
    public function withOutputType(string $type): self
    {
        $this->outputType = $type;
        return $this;
    }

    /**
     * Clone the agent with the given attributes.
     *
     * @param array $attributes
     * @return self
     */
    public function clone(array $attributes = []): self
    {
        $clone = new self($this->name, $this->instructions, $this->modelSettings);
        
        $clone->handoffDescription = $this->handoffDescription;
        $clone->handoffs = $this->handoffs;
        $clone->model = $this->model;
        $clone->tools = $this->tools;
        $clone->inputGuardrails = $this->inputGuardrails;
        $clone->outputGuardrails = $this->outputGuardrails;
        $clone->outputType = $this->outputType;
        
        foreach ($attributes as $key => $value) {
            $clone->{$key} = $value;
        }
        
        return $clone;
    }

    /**
     * Transform this agent into a tool, callable by other agents.
     *
     * @param string|null $toolName
     * @param string|null $toolDescription
     * @param callable|null $customOutputExtractor
     * @return Tool
     */
    public function asTool(?string $toolName = null, ?string $toolDescription = null, ?callable $customOutputExtractor = null): Tool
    {
        return new Tool(
            $toolName ?? $this->transformStringToFunctionStyle($this->name),
            $toolDescription ?? "",
            function ($context, $input) use ($customOutputExtractor) {
                $runner = app(Runner::class);
                $output = $runner->run($this, $input, $context);
                
                if ($customOutputExtractor) {
                    return $customOutputExtractor($output);
                }
                
                return $output->getTextOutput();
            }
        );
    }

    /**
     * Get the system prompt for the agent.
     *
     * @param RunContext $context
     * @return string|null
     */
    public function getSystemPrompt(RunContext $context): ?string
    {
        if (is_string($this->instructions)) {
            return $this->instructions;
        } elseif ($this->instructions instanceof Closure) {
            return ($this->instructions)($context, $this);
        }
        
        return null;
    }

    /**
     * Transform a string to function style.
     *
     * @param string $input
     * @return string
     */
    protected function transformStringToFunctionStyle(string $input): string
    {
        return strtolower(preg_replace('/\s+/', '_', $input));
    }

    /**
     * Get the name of the agent.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the handoff description.
     *
     * @return string|null
     */
    public function getHandoffDescription(): ?string
    {
        return $this->handoffDescription;
    }

    /**
     * Get the handoffs.
     *
     * @return array
     */
    public function getHandoffs(): array
    {
        return $this->handoffs;
    }

    /**
     * Get the model.
     *
     * @return string|Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the model settings.
     *
     * @return ModelSettings
     */
    public function getModelSettings(): ModelSettings
    {
        return $this->modelSettings;
    }

    /**
     * Get the tools.
     *
     * @return array
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * Get the input guardrails.
     *
     * @return array
     */
    public function getInputGuardrails(): array
    {
        return $this->inputGuardrails;
    }

    /**
     * Get the output guardrails.
     *
     * @return array
     */
    public function getOutputGuardrails(): array
    {
        return $this->outputGuardrails;
    }

    /**
     * Get the output type.
     *
     * @return string|null
     */
    public function getOutputType(): ?string
    {
        return $this->outputType;
    }
}