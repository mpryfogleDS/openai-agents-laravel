<?php

namespace JawadAshraf\OpenAI\Agents\Items;

class RunItem
{
    /**
     * The type of the item.
     *
     * @var string
     */
    protected string $type;
    
    /**
     * The content of the item.
     *
     * @var mixed
     */
    protected $content;
    
    /**
     * Create a new run item instance.
     *
     * @param string $type
     * @param mixed $content
     */
    public function __construct(string $type, $content)
    {
        $this->type = $type;
        $this->content = $content;
    }
    
    /**
     * Get the type of the item.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * Get the content of the item.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Convert this item to an input item.
     *
     * @return array
     */
    public function toInputItem(): array
    {
        if ($this->type === 'ai_message') {
            return [
                'role' => 'assistant',
                'content' => is_string($this->content) ? $this->content : $this->content['content'] ?? null,
                'tool_calls' => $this->content['tool_calls'] ?? null,
            ];
        } elseif ($this->type === 'tool_result') {
            return [
                'role' => 'tool',
                'tool_call_id' => $this->content['tool_call_id'] ?? null,
                'name' => $this->content['tool_name'] ?? null,
                'content' => $this->content['result'] ?? null,
            ];
        } elseif ($this->type === 'user_message') {
            return [
                'role' => 'user',
                'content' => $this->content,
            ];
        } else {
            return [
                'type' => $this->type,
                'content' => $this->content,
            ];
        }
    }
}