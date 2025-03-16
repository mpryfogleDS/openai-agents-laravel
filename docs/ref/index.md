# API Reference

This section contains detailed API reference documentation for the OpenAI Agents for Laravel package.

## Core Components

- [Agent](agent.md) - Configuration for agents
- [Runner](runner.md) - Execute agent workflows
- [RunConfig](run_config.md) - Configure agent runs
- [RunContext](run_context.md) - Context for agent runs
- [RunResult](result.md) - Results from agent runs

## Tools

- [Tool](tool.md) - Function tools for agents
- [FunctionTool](function_tool.md) - Helper for creating tools

## Handoffs

- [Handoff](handoffs.md) - Transfer control between agents
- [HandoffInputFilter](handoff_input_filter.md) - Filter conversation history before handoffs

## Guardrails

- [InputGuardrail](input_guardrail.md) - Validate agent inputs
- [OutputGuardrail](output_guardrail.md) - Validate agent outputs
- [GuardrailOutput](guardrail_output.md) - Result of guardrail checks

## Models

- [Model](models/interface.md) - Interface for language models
- [ModelSettings](model_settings.md) - Configure model parameters
- [OpenAIChatCompletionsModel](models/openai_chatcompletions.md) - OpenAI model implementation

## Tracing

- [Trace](tracing/trace.md) - Trace manager
- [TraceSpan](tracing/span.md) - Individual spans in a trace
- [TraceProcessorInterface](tracing/processor_interface.md) - Interface for trace processors

## Exceptions

- [AgentsException](exceptions.md) - Base exception class
- [MaxTurnsExceeded](exceptions.md#maxturnsexceeded) - Exception for maximum turns exceeded
- [InputGuardrailTripwireTriggered](exceptions.md#inputguardrailtriphiretriggered) - Exception for input guardrail failures
- [OutputGuardrailTripwireTriggered](exceptions.md#outputguardrailtriphiretriggered) - Exception for output guardrail failures