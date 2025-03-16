<?php

namespace JawadAshraf\OpenAI\Agents\Handoffs;

use OpenAI\Agents\Agent;

class HandoffHelper
{
    /**
     * Create a handoff to an agent.
     *
     * @param Agent $agent
     * @param string|null $description
     * @param HandoffInputFilter|null $inputFilter
     * @return Handoff
     */
    public static function create(Agent $agent, ?string $description = null, ?HandoffInputFilter $inputFilter = null): Handoff
    {
        return new Handoff($agent, $description, $inputFilter);
    }
}