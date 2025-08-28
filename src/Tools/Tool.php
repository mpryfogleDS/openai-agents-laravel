<?php

namespace OpenAI\Agents\Tools;

use OpenAI\Agents\RunContext;
use Closure;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionNamedType;

class Tool
{
    /**
     * The name of the tool.
     *
     * @var string
     */
    protected string $name;
    
    /**
     * The description of the tool.
     *
     * @var string
     */
    protected string $description;
    
    /**
     * The parameters of the tool.
     *
     * @var array
     */
    protected array $parameters;
    
    /**
     * The callback function of the tool.
     *
     * @var Closure
     */
    protected Closure $callback;
    
    /**
     * Create a new tool instance.
     *
     * @param string $name
     * @param string $description
     * @param Closure $callback
     * @param array|null $parameters
     */
    public function __construct(string $name, string $description, Closure $callback, ?array $parameters = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->callback = $callback;
        $this->parameters = $parameters ?? $this->inferParameters($callback);
    }
    
    /**
     * Create a new tool from a function.
     *
     * @param callable $callback
     * @param string|null $name
     * @param string|null $description
     * @return static
     */
    public static function fromFunction(callable $callback, ?string $name = null, ?string $description = null): self
    {
        $reflection = new ReflectionFunction($callback);
        
        // If name is not provided, use the function name
        $name = $name ?? $reflection->getName();
        
        // If description is not provided, use the function docblock
        $description = $description ?? '';
        if (!$description && $reflection->getDocComment()) {
            $docblock = $reflection->getDocComment();
            $description = trim(preg_replace('/^\s*\*\s*/m', '', substr($docblock, 3, -2)));
        }
        
        return new self($name, $description, $reflection->getClosure());
    }
    
    /**
     * Execute the tool.
     *
     * @param RunContext $context
     * @param array $arguments
     * @return mixed
     */
    public function execute(RunContext $context, array $arguments)
    {
        return ($this->callback)($context, $arguments);
    }
    
    /**
     * Get the name of the tool.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Get the description of the tool.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Get the parameters of the tool.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    /**
     * Infer parameters from the callback function.
     *
     * @param Closure $callback
     * @return array
     */
    protected function inferParameters(Closure $callback): array
    {
        $reflection = new ReflectionFunction($callback);
        $parameters = $reflection->getParameters();
        
        // Skip the first parameter, which is the context
        $parameters = array_slice($parameters, 1);
        
        if (empty($parameters)) {
            return [
                'type' => 'object',
                'properties' => [],
            ];
        }
        
        $properties = [];
        $required = [];
        
        foreach ($parameters as $parameter) {
            $properties[$parameter->getName()] = $this->getParameterSchema($parameter);
            
            if (!$parameter->isOptional()) {
                $required[] = $parameter->getName();
            }
        }
        
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }
    
    /**
     * Get the schema for a reflection parameter.
     *
     * @param ReflectionParameter $parameter
     * @return array
     */
    protected function getParameterSchema(ReflectionParameter $parameter): array
    {
        $type = $parameter->getType();
        
        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
            
            switch ($typeName) {
                case 'int':
                    return ['type' => 'integer'];
                case 'float':
                    return ['type' => 'number'];
                case 'bool':
                    return ['type' => 'boolean'];
                case 'array':
                    return ['type' => 'array'];
                case 'string':
                default:
                    return ['type' => 'string'];
            }
        }
        
        // Default to string if no type is specified
        return ['type' => 'string'];
    }
}