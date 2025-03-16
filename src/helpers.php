<?php

use OpenAI\Agents\Agent;
use OpenAI\Agents\Handoffs\Handoff;
use OpenAI\Agents\Handoffs\HandoffInputFilter;
use OpenAI\Agents\Tools\Tool;
use OpenAI\Agents\Tools\FunctionTool;

if (!function_exists('agent')) {
    /**
     * Create a new agent.
     *
     * @param string $name
     * @param string|\Closure|null $instructions
     * @return \OpenAI\Agents\Agent
     */
    function agent(string $name, $instructions = null): Agent
    {
        return new Agent($name, $instructions);
    }
}

if (!function_exists('handoff')) {
    /**
     * Create a handoff to an agent.
     *
     * @param \OpenAI\Agents\Agent $agent
     * @param string|null $description
     * @param \OpenAI\Agents\Handoffs\HandoffInputFilter|null $inputFilter
     * @return \OpenAI\Agents\Handoffs\Handoff
     */
    function handoff(Agent $agent, ?string $description = null, ?HandoffInputFilter $inputFilter = null): Handoff
    {
        return new Handoff($agent, $description, $inputFilter);
    }
}

if (!function_exists('function_tool')) {
    /**
     * Create a tool from a function.
     *
     * @param callable $callback
     * @param string|null $name
     * @param string|null $description
     * @return \OpenAI\Agents\Tools\Tool
     */
    function function_tool(callable $callback, ?string $name = null, ?string $description = null): Tool
    {
        return FunctionTool::create($callback, $name, $description);
    }
}

if (!function_exists('function_tool_decorator')) {
    /**
     * Create a function that creates a tool.
     *
     * @param string|null $nameOverride
     * @param string|null $descriptionOverride
     * @param array|null $parametersOverride
     * @return \Closure
     */
    function function_tool_decorator(?string $nameOverride = null, ?string $descriptionOverride = null, ?array $parametersOverride = null): Closure
    {
        return FunctionTool::decorator($nameOverride, $descriptionOverride, $parametersOverride);
    }
}