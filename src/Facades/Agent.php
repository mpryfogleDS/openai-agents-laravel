<?php

namespace OpenAI\Agents\Facades;

use Illuminate\Support\Facades\Facade;
use OpenAI\Agents\Agent as AgentClass;
use OpenAI\Agents\Models\ModelSettings;

/**
 * @method static \OpenAI\Agents\Agent create(string $name, string|\Closure|null $instructions = null, ?\OpenAI\Agents\Models\ModelSettings $modelSettings = null)
 * @method static \OpenAI\Agents\Agent withHandoffDescription(string $description)
 * @method static \OpenAI\Agents\Agent withHandoffs(array $handoffs)
 * @method static \OpenAI\Agents\Agent withModel(string|\OpenAI\Agents\Models\Model $model)
 * @method static \OpenAI\Agents\Agent withModelSettings(\OpenAI\Agents\Models\ModelSettings $settings)
 * @method static \OpenAI\Agents\Agent withTools(array $tools)
 * @method static \OpenAI\Agents\Agent withInputGuardrails(array $guardrails)
 * @method static \OpenAI\Agents\Agent withOutputGuardrails(array $guardrails)
 * @method static \OpenAI\Agents\Agent withOutputType(string $type)
 * @method static \OpenAI\Agents\Agent clone(array $attributes = [])
 * @method static \OpenAI\Agents\Tools\Tool asTool(?string $toolName = null, ?string $toolDescription = null, ?callable $customOutputExtractor = null)
 * @method static string|null getSystemPrompt(\OpenAI\Agents\RunContext $context)
 */
class Agent extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AgentClass::class;
    }
    
    /**
     * Create a new agent instance.
     *
     * @param string $name
     * @param string|\Closure|null $instructions
     * @param ModelSettings|null $modelSettings
     * @return \OpenAI\Agents\Agent
     */
    public static function create(string $name, $instructions = null, ?ModelSettings $modelSettings = null)
    {
        return new AgentClass($name, $instructions, $modelSettings);
    }
}