<?php

namespace JawadAshraf\OpenAI\Agents\Facades;

use Illuminate\Support\Facades\Facade;
use OpenAI\Agents\Runner as RunnerClass;
use OpenAI\Agents\Agent;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Result\RunResult;
use OpenAI\Agents\Result\RunResultStreaming;

/**
 * @method static \OpenAI\Agents\Result\RunResult run(\OpenAI\Agents\Agent $startingAgent, string|array $input, mixed $context = null, ?int $maxTurns = null, ?\OpenAI\Agents\RunConfig $runConfig = null)
 * @method static \OpenAI\Agents\Result\RunResultStreaming runStreamed(\OpenAI\Agents\Agent $startingAgent, string|array $input, mixed $context = null, ?int $maxTurns = null, ?\OpenAI\Agents\RunConfig $runConfig = null)
 */
class Runner extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RunnerClass::class;
    }
    
    /**
     * Run a workflow starting at the given agent.
     *
     * @param Agent $startingAgent
     * @param string|array $input
     * @param mixed $context
     * @param int|null $maxTurns
     * @param RunConfig|null $runConfig
     * @return RunResult
     */
    public static function runSync(Agent $startingAgent, $input, $context = null, ?int $maxTurns = null, ?RunConfig $runConfig = null): RunResult
    {
        return app(RunnerClass::class)->run($startingAgent, $input, $context, $maxTurns, $runConfig);
    }
}