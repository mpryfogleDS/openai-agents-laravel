# Handoffs

Handoffs allow agents to transfer control to other agents for specific tasks. This enables you to build complex workflows with separation of concerns, where each agent specializes in a specific domain.

## Basic handoffs

To use handoffs, you need to:

1. Create specialized agents
2. Add them as handoffs to your main agent

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;

// Create specialized agents
$spanishAgent = Agent::create(
    "Spanish Agent",
    "You only speak Spanish."
);

$englishAgent = Agent::create(
    "English Agent",
    "You only speak English."
);

// Create a main agent with handoffs
$triageAgent = Agent::create(
    "Triage Agent",
    "Handoff to the appropriate agent based on the language of the request."
)->withHandoffs([$spanishAgent, $englishAgent]);

// Run the agent
$result = Runner::runSync($triageAgent, "Hola, ¿cómo estás?");
echo $result->getTextOutput();
// Output: ¡Hola! Estoy bien, gracias por preguntar. ¿Y tú, cómo estás?
```

## Creating handoffs

There are several ways to create handoffs:

### Using an array of agents

```php
$mainAgent = Agent::create("Main Agent", "You triage user requests.")
    ->withHandoffs([$specialistAgent1, $specialistAgent2, $specialistAgent3]);
```

### Using the handoff helper

```php
use function OpenAI\Agents\handoff;

$handoff1 = handoff($specialistAgent1, "Handles technical questions");
$handoff2 = handoff($specialistAgent2, "Handles billing inquiries");

$mainAgent = Agent::create("Main Agent", "You triage user requests.")
    ->withHandoffs([$handoff1, $handoff2]);
```

### Using the Handoff class

```php
use OpenAI\Agents\Handoffs\Handoff;
use OpenAI\Agents\Handoffs\HandoffInputFilter;

// Create a custom input filter
class SanitizeInputFilter implements HandoffInputFilter
{
    public function filter(array $input, $context, $handoff): array
    {
        // Remove sensitive information before handoff
        foreach ($input as $key => $item) {
            if (isset($item['content'])) {
                $item['content'] = preg_replace('/\b\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\b/', '[CREDIT CARD REDACTED]', $item['content']);
                $input[$key] = $item;
            }
        }
        
        return $input;
    }
}

// Create a handoff with a custom filter
$handoff = new Handoff(
    $specialistAgent,
    "Handles payment processing",
    new SanitizeInputFilter()
);

$mainAgent = Agent::create("Main Agent", "You triage user requests.")
    ->withHandoffs([$handoff]);
```

## Handoff descriptions

Handoff descriptions help the agent decide which handoff to use:

```php
$technicalSupportAgent = Agent::create(
    "Technical Support",
    "You help customers with technical issues."
)->withHandoffDescription("Specialist for technical problems with products or services");

$billingAgent = Agent::create(
    "Billing Support",
    "You help customers with billing and payment issues."
)->withHandoffDescription("Specialist for billing inquiries, payments, and refunds");

$mainAgent = Agent::create(
    "Customer Support",
    "You triage customer inquiries and hand off to specialists when appropriate."
)->withHandoffs([$technicalSupportAgent, $billingAgent]);
```

## Input filters

Input filters allow you to modify conversation history before handing off to another agent:

```php
use OpenAI\Agents\Handoffs\HandoffInputFilter;
use OpenAI\Agents\RunContext;
use OpenAI\Agents\Handoffs\Handoff;

class SummarizeInputFilter implements HandoffInputFilter
{
    public function filter(array $input, RunContext $context, Handoff $handoff): array
    {
        // Keep only the first user message and the most recent 3 messages
        if (count($input) > 4) {
            $firstMessage = $input[0];
            $recentMessages = array_slice($input, -3);
            
            // Create a summary message
            $summaryMessage = [
                'role' => 'system',
                'content' => 'The conversation has been summarized for brevity.',
            ];
            
            return [$firstMessage, $summaryMessage, ...$recentMessages];
        }
        
        return $input;
    }
}

// Create a handoff with the summarize filter
$handoff = handoff($specialistAgent, "Handles complex inquiries")
    ->withInputFilter(new SummarizeInputFilter());
```

## Global input filters

You can also set a global input filter that applies to all handoffs:

```php
use OpenAI\Agents\Facades\Agent;
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunConfig;
use OpenAI\Agents\Handoffs\HandoffInputFilter;

class GlobalFilter implements HandoffInputFilter
{
    public function filter(array $input, $context, $handoff): array
    {
        // Add a system message to all handoffs
        $systemMessage = [
            'role' => 'system',
            'content' => "This conversation has been handed off from the main agent. The user's account ID is {$context->getContext()['user_id']}.",
        ];
        
        return [$systemMessage, ...$input];
    }
}

// Create a run config with a global input filter
$runConfig = new RunConfig();
$runConfig->withHandoffInputFilter(new GlobalFilter());

// Run the agent with the config
$result = Runner::runSync($mainAgent, "I need help with my account", ['user_id' => 12345], null, $runConfig);
```

## Handoff flow

When an agent hands off to another agent:

1. The main agent decides it needs to hand off to a specialist
2. The conversation history is optionally filtered
3. The specialist agent takes over, receiving the filtered conversation history
4. The specialist agent starts generating responses
5. The loop continues until the specialist agent produces a final output
6. The final result includes information about which agent produced the output

```php
$result = Runner::runSync($mainAgent, "Tengo una pregunta sobre mi pedido");

// Check which agent produced the final output
if ($result->getLastAgent()->getName() === "Spanish Agent") {
    // The Spanish agent handled the request
}
```

## Multi-step handoffs

Handoffs can form a chain, with each agent handing off to the next:

```php
$triageAgent = Agent::create(
    "Triage Agent",
    "You classify customer inquiries and route them to the appropriate department."
)->withHandoffs([$technicalAgent, $billingAgent, $salesAgent]);

$technicalAgent = Agent::create(
    "Technical Agent",
    "You handle technical support inquiries."
)->withHandoffs([$softwareAgent, $hardwareAgent]);

$softwareAgent = Agent::create(
    "Software Agent",
    "You handle software-specific technical issues."
);

// A request might flow: Triage → Technical → Software
$result = Runner::runSync($triageAgent, "My software is crashing when I try to save files.");
```

## Next steps

- Learn about [Guardrails](guardrails.md)
- Learn about [Tracing](tracing.md)
- Learn about [Models](models.md)