<?php

namespace OpenAI\Agents\Models;

interface ModelProviderInterface
{
    /**
     * Get a model by name.
     *
     * @param string|null $modelName
     * @return Model
     */
    public function getModel(?string $modelName = null): Model;
}