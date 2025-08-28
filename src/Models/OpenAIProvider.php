<?php

namespace OpenAI\Agents\Models;

use OpenAI\Client;
use OpenAI\Factory;

class OpenAIProvider implements ModelProviderInterface
{
    /**
     * The OpenAI API key.
     *
     * @var string|null
     */
    protected ?string $apiKey;
    
    /**
     * The OpenAI organization ID.
     *
     * @var string|null
     */
    protected ?string $organization;
    
    /**
     * Create a new OpenAI provider instance.
     *
     * @param string|null $apiKey
     * @param string|null $organization
     */
    public function __construct(?string $apiKey = null, ?string $organization = null)
    {
        $this->apiKey = $apiKey ?? config('agents.api_key');
        $this->organization = $organization;
    }
    
    /**
     * Get a model by name.
     *
     * @param string|null $modelName
     * @return Model
     */
    public function getModel(?string $modelName = null): Model
    {
        $modelName = $modelName ?? config('agents.default_model');
        
        return new OpenAIChatCompletionsModel(
            $this->createClient(),
            $modelName
        );
    }
    
    /**
     * Create an OpenAI client.
     *
     * @return Client
     */
    protected function createClient(): Client
    {
        $factory = new Factory();
        
        if ($this->organization) {
            $factory = $factory->withOrganization($this->organization);
        }
        
        return $factory->withApiKey($this->apiKey)->make();
    }
}