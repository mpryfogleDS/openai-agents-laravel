<?php

namespace OpenAI\Agents\Handoffs;

use OpenAI\Agents\RunContext;

interface HandoffInputFilter
{
    /**
     * Filter the input before handing off to another agent.
     *
     * @param array $input
     * @param RunContext $context
     * @param Handoff $handoff
     * @return array
     */
    public function filter(array $input, RunContext $context, Handoff $handoff): array;
}