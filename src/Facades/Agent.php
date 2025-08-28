<?php

namespace OpenAI\Agents\Facades;

use Illuminate\Support\Facades\Facade;
use OpenAI\Agents\Agent as AgentClass;
use OpenAI\Agents\Models\Model;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Tools\Tool;

/**
 * @method static AgentClass create(string $name, string|\Closure|null $instructions = null, ?ModelSettings $modelSettings = null)
 * @method static AgentClass withHandoffDescription(string $description)
 * @method static AgentClass withHandoffs(array $handoffs)
 * @method static AgentClass withModel(string|Model $model)
 * @method static AgentClass withModelSettings(ModelSettings $settings)
 * @method static AgentClass withTools(array $tools)
 * @method static AgentClass withInputGuardrails(array $guardrails)
 * @method static AgentClass withOutputGuardrails(array $guardrails)
 * @method static AgentClass withOutputType(string $type)
 * @method static AgentClass clone (array $attributes = [])
 * @method static Tool asTool(?string $toolName = null, ?string $toolDescription = null, ?callable $customOutputExtractor = null)
 * @method static string|null getSystemPrompt(RunContext $context)
 */
class Agent extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AgentClass::class;
    }

}