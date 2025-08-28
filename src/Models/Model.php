<?php

namespace OpenAI\Agents\Models;

use OpenAI\Agents\Items\ModelResponse;

interface Model
{
    /**
     * Get a response from the model.
     *
     * @param string|null $systemInstructions
     * @param array $input
     * @param ModelSettings $modelSettings
     * @param array $tools
     * @param string|null $outputType
     * @param array $handoffs
     * @param array $tracing
     * @return ModelResponse
     */
    public function getResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): ModelResponse;
    
    /**
     * Stream a response from the model.
     *
     * @param string|null $systemInstructions
     * @param array $input
     * @param ModelSettings $modelSettings
     * @param array $tools
     * @param string|null $outputType
     * @param array $handoffs
     * @param array $tracing
     * @return \Generator
     */
    public function streamResponse(
        ?string $systemInstructions,
        array $input,
        ModelSettings $modelSettings,
        array $tools,
        ?string $outputType,
        array $handoffs,
        array $tracing = []
    ): \Generator;
}