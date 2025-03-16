<?php

namespace JawadAshraf\OpenAI\Agents\Tools;

use Closure;

class FunctionTool
{
    /**
     * Create a tool from a function.
     *
     * @param callable $callback
     * @param string|null $name
     * @param string|null $description
     * @return Tool
     */
    public static function create(callable $callback, ?string $name = null, ?string $description = null): Tool
    {
        return Tool::fromFunction($callback, $name, $description);
    }
    
    /**
     * Create a function that creates a tool.
     *
     * @param string|null $nameOverride
     * @param string|null $descriptionOverride
     * @param array|null $parametersOverride
     * @return Closure
     */
    public static function decorator(?string $nameOverride = null, ?string $descriptionOverride = null, ?array $parametersOverride = null): Closure
    {
        return function (callable $callback) use ($nameOverride, $descriptionOverride, $parametersOverride) {
            return new Tool(
                $nameOverride ?? self::getFunctionName($callback),
                $descriptionOverride ?? self::getFunctionDescription($callback),
                Closure::fromCallable($callback),
                $parametersOverride
            );
        };
    }
    
    /**
     * Get the name of a function.
     *
     * @param callable $callback
     * @return string
     */
    protected static function getFunctionName(callable $callback): string
    {
        $reflection = new \ReflectionFunction(Closure::fromCallable($callback));
        return $reflection->getName();
    }
    
    /**
     * Get the description of a function from its docblock.
     *
     * @param callable $callback
     * @return string
     */
    protected static function getFunctionDescription(callable $callback): string
    {
        $reflection = new \ReflectionFunction(Closure::fromCallable($callback));
        
        if (!$reflection->getDocComment()) {
            return '';
        }
        
        $docblock = $reflection->getDocComment();
        return trim(preg_replace('/^\s*\*\s*/m', '', substr($docblock, 3, -2)));
    }
}