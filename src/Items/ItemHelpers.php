<?php

namespace OpenAI\Agents\Items;

class ItemHelpers
{
    /**
     * Convert input to a list of input items.
     *
     * @param string|array $input
     * @return array
     */
    public static function inputToNewInputList($input): array
    {
        if (is_string($input)) {
            return [
                [
                    'role' => 'user',
                    'content' => $input,
                ],
            ];
        }
        
        return $input;
    }
    
    /**
     * Extract text message outputs from a list of items.
     *
     * @param array $items
     * @return string
     */
    public static function textMessageOutputs(array $items): string
    {
        $outputs = [];
        
        foreach ($items as $item) {
            if ($item instanceof RunItem && $item->getType() === 'ai_message') {
                $content = $item->getContent();
                
                if (is_string($content)) {
                    $outputs[] = $content;
                } elseif (is_array($content) && isset($content['content'])) {
                    $outputs[] = $content['content'];
                }
            }
        }
        
        return implode("\n", $outputs);
    }
}