# Guardrails

Guardrails allow you to enforce constraints on agent inputs and outputs. They can be used to validate, filter, or reject content based on custom rules.

## Types of guardrails

There are two types of guardrails:

1. **Input Guardrails**: Run before the agent processes the input
2. **Output Guardrails**: Run after the agent generates its final output

## Creating input guardrails

To create an input guardrail, you need to extend the `InputGuardrail` class:

```php
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Agent;

class ContentFilterGuardrail extends InputGuardrail
{
    public function check($input, RunContext $context, Agent $agent): GuardrailOutput
    {
        // Convert input to string if it's an array
        $text = is_array($input) ? json_encode($input) : $input;
        
        // Check for inappropriate content
        if (preg_match('/\b(inappropriate|offensive|harmful)\b/i', $text)) {
            return GuardrailOutput::failed("Input contains potentially inappropriate content");
        }
        
        return GuardrailOutput::passed("Input passed content filter");
    }
}
```

## Creating output guardrails

To create an output guardrail, you need to extend the `OutputGuardrail` class:

```php
use OpenAI\Agents\Guardrails\OutputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Agent;

class FactualityGuardrail extends OutputGuardrail
{
    public function check($output, RunContext $context, Agent $agent): GuardrailOutput
    {
        // This is a simplified example. In reality, you might:
        // - Call a fact-checking model or service
        // - Check against a database of facts
        // - Use information in the context to validate claims
        
        // Check if output contains a disclaimer
        if (!str_contains($output, "This information is believed to be accurate")) {
            return GuardrailOutput::failed("Output missing factuality disclaimer");
        }
        
        return GuardrailOutput::passed("Output includes factuality disclaimer");
    }
}
```

## Adding guardrails to agents

You can add guardrails to an agent using the `withInputGuardrails` and `withOutputGuardrails` methods:

```php
use OpenAI\Agents\Facades\Agent;

$agent = Agent::create("Safe Assistant", "You are a helpful assistant.")
    ->withInputGuardrails([new ContentFilterGuardrail()])
    ->withOutputGuardrails([new FactualityGuardrail()]);
```

## Global guardrails

You can also set global guardrails that apply to all agents in a run:

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunConfig;

$runConfig = new RunConfig();
$runConfig->withInputGuardrails([new ContentFilterGuardrail()])
    ->withOutputGuardrails([new FactualityGuardrail()]);

$result = Runner::runSync($agent, "Tell me about Paris", null, null, $runConfig);
```

## Guardrail outputs

Guardrails return a `GuardrailOutput` object that has two main properties:

- `tripwireTriggered`: A boolean indicating whether the guardrail was triggered
- `message`: An optional message explaining why the guardrail was triggered or providing additional information

## Guardrail results

The results of guardrail checks are included in the run result:

```php
$result = Runner::runSync($agent, "Tell me about Paris");

// Check input guardrail results
foreach ($result->getInputGuardrailResults() as $guardrailResult) {
    echo "Guardrail: " . get_class($guardrailResult->guardrail) . "\n";
    echo "Message: " . $guardrailResult->output->message . "\n";
    echo "Triggered: " . ($guardrailResult->output->tripwireTriggered ? "Yes" : "No") . "\n";
}

// Check output guardrail results
foreach ($result->getOutputGuardrailResults() as $guardrailResult) {
    echo "Guardrail: " . get_class($guardrailResult->guardrail) . "\n";
    echo "Message: " . $guardrailResult->output->message . "\n";
    echo "Triggered: " . ($guardrailResult->output->tripwireTriggered ? "Yes" : "No") . "\n";
}
```

## Handling guardrail exceptions

When a guardrail tripwire is triggered, an exception is thrown. You can catch these exceptions to handle them gracefully:

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\Exceptions\InputGuardrailTripwireTriggered;
use OpenAI\Agents\Exceptions\OutputGuardrailTripwireTriggered;

try {
    $result = Runner::runSync($agent, "Tell me something inappropriate");
    echo $result->getTextOutput();
} catch (InputGuardrailTripwireTriggered $e) {
    $guardrailResult = $e->getResult();
    echo "Input guardrail triggered: " . $guardrailResult->output->message;
    
    // Log the incident
    // Notify moderation team
    // Return a user-friendly message
    return response()->json([
        'error' => 'Your message was flagged by our content filter.',
        'message' => 'Please rephrase your request to avoid potentially inappropriate content.'
    ], 400);
} catch (OutputGuardrailTripwireTriggered $e) {
    $guardrailResult = $e->getResult();
    echo "Output guardrail triggered: " . $guardrailResult->output->message;
    
    // Handle output guardrail exception
    return response()->json([
        'error' => 'Our system could not generate a safe response.',
        'message' => 'Please try a different question or approach.'
    ], 400);
}
```

## Example guardrails

### PII (Personally Identifiable Information) Filter

```php
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;

class PIIFilter extends InputGuardrail
{
    public function check($input, $context, $agent): GuardrailOutput
    {
        $text = is_array($input) ? json_encode($input) : $input;
        
        // Check for common PII patterns
        $patterns = [
            // Credit card
            '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/' => 'credit card number',
            // Social Security Number (US)
            '/\b\d{3}[- ]?\d{2}[- ]?\d{4}\b/' => 'SSN',
            // Email
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/' => 'email address',
            // Phone
            '/\b\d{3}[- ]?\d{3}[- ]?\d{4}\b/' => 'phone number',
        ];
        
        $foundPII = [];
        
        foreach ($patterns as $pattern => $label) {
            if (preg_match($pattern, $text)) {
                $foundPII[] = $label;
            }
        }
        
        if (!empty($foundPII)) {
            return GuardrailOutput::failed("Input contains PII: " . implode(', ', $foundPII));
        }
        
        return GuardrailOutput::passed("No PII detected");
    }
}
```

### Language Filter

```php
use OpenAI\Agents\Guardrails\InputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;

class LanguageFilter extends InputGuardrail
{
    protected $allowedLanguages = ['en', 'es', 'fr'];
    
    public function check($input, $context, $agent): GuardrailOutput
    {
        $text = is_string($input) ? $input : (is_array($input) ? json_encode($input) : (string)$input);
        
        // In a real implementation, you would use a language detection service
        // This is a simplified example using a common library
        $detectedLanguage = $this->detectLanguage($text);
        
        if (!in_array($detectedLanguage, $this->allowedLanguages)) {
            return GuardrailOutput::failed("Unsupported language detected: {$detectedLanguage}");
        }
        
        return GuardrailOutput::passed("Language supported: {$detectedLanguage}");
    }
    
    protected function detectLanguage(string $text): string
    {
        // Simplified language detection
        // In reality, you would use a library like language-detection or call an API
        $langTests = [
            'en' => ['the', 'and', 'is', 'in', 'to', 'it'],
            'es' => ['el', 'la', 'es', 'en', 'y', 'por'],
            'fr' => ['le', 'la', 'est', 'et', 'en', 'je'],
        ];
        
        $scores = [];
        
        foreach ($langTests as $lang => $words) {
            $score = 0;
            foreach ($words as $word) {
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
                    $score++;
                }
            }
            $scores[$lang] = $score;
        }
        
        return array_search(max($scores), $scores) ?: 'unknown';
    }
}
```

### Output Factuality Check

```php
use OpenAI\Agents\Guardrails\OutputGuardrail;
use OpenAI\Agents\Guardrails\GuardrailOutput;
use Illuminate\Support\Facades\Http;

class FactualityCheck extends OutputGuardrail
{
    public function check($output, $context, $agent): GuardrailOutput
    {
        // In a real implementation, you might:
        // 1. Use another LLM call to fact-check the output
        // 2. Check claims against a database of facts
        // 3. Use a specialized fact-checking API
        
        // Example using another LLM call (simplified)
        $apiKey = config('agents.api_key');
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a fact-checker. Analyze the following text and identify any factual errors.',
                ],
                [
                    'role' => 'user',
                    'content' => $output,
                ],
            ],
            'max_tokens' => 500,
        ]);
        
        $factCheckResult = $response->json()['choices'][0]['message']['content'] ?? '';
        
        if (str_contains(strtolower($factCheckResult), 'error') || 
            str_contains(strtolower($factCheckResult), 'incorrect') ||
            str_contains(strtolower($factCheckResult), 'inaccurate')) {
            return GuardrailOutput::failed("Factuality check failed: {$factCheckResult}");
        }
        
        return GuardrailOutput::passed("Passed factuality check");
    }
}
```

## Next steps

- Learn about [Models](models.md)
- Learn about [Tracing](tracing.md)
- Learn about [Context](context.md)