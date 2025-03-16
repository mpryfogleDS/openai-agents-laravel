<?php

namespace JawadAshraf\OpenAI\Agents\Items;

class ModelResponse
{
    /**
     * The output from the model.
     *
     * @var mixed
     */
    protected $output;
    
    /**
     * The usage statistics.
     *
     * @var Usage
     */
    protected Usage $usage;
    
    /**
     * The ID that can be referenced in future API calls.
     *
     * @var string|null
     */
    protected ?string $referenceableId;
    
    /**
     * Create a new model response instance.
     *
     * @param mixed $output
     * @param Usage|null $usage
     * @param string|null $referenceableId
     */
    public function __construct($output, ?Usage $usage = null, ?string $referenceableId = null)
    {
        $this->output = $output;
        $this->usage = $usage ?? new Usage();
        $this->referenceableId = $referenceableId;
    }
    
    /**
     * Get the output from the model.
     *
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Get the usage statistics.
     *
     * @return Usage
     */
    public function getUsage(): Usage
    {
        return $this->usage;
    }
    
    /**
     * Get the ID that can be referenced in future API calls.
     *
     * @return string|null
     */
    public function getReferenceableId(): ?string
    {
        return $this->referenceableId;
    }
}