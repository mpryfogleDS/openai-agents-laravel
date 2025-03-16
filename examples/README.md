# OpenAI Agents for Laravel Examples

This directory contains examples of how to use the OpenAI Agents SDK for Laravel.

## Standalone Examples

These examples can be run directly with PHP CLI:

- `hello_world.php`: A simple example showing how to create an agent and run it.
- `handoffs.php`: An example demonstrating how agents can hand off to other agents.
- `functions.php`: An example showing how to use function tools with agents.

To run these examples:

```bash
# Set your OpenAI API key
export OPENAI_API_KEY=your-api-key

# Run the example
php examples/hello_world.php
```

## Laravel Examples

These examples show how to integrate OpenAI Agents into a Laravel application:

- `AgentController.php`: A Laravel controller with examples of handling synchronous and streaming agent responses.

### Using the Controller

To use the `AgentController` in your Laravel application:

1. Copy the file to your `app/Http/Controllers` directory.
2. Add the following routes to your `routes/web.php` file:

```php
Route::post('/chat', [AgentController::class, 'chat']);
Route::post('/stream', [AgentController::class, 'stream']);
```

3. Make API calls to these endpoints:

```javascript
// Synchronous chat
fetch('/chat', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ message: 'What's the weather in Tokyo?' })
})
.then(response => response.json())
.then(data => console.log(data.message));

// Streaming chat
const eventSource = new EventSource('/stream', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ message: 'Tell me a story about a robot.' })
});

eventSource.addEventListener('message', (event) => {
  const data = JSON.parse(event.data);
  
  if (data.type === 'token') {
    // Handle streaming tokens
    console.log(data.content);
  } else if (data.type === 'complete') {
    // Handle completion
    console.log('Completed:', data.content);
    eventSource.close();
  }
});
```