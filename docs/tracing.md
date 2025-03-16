# Tracing

The OpenAI Agents for Laravel package includes a tracing system that helps you debug, monitor, and optimize your agent workflows.

## How tracing works

Tracing in the Agents package works by recording spans of activity during agent runs. Each span represents a specific operation, such as:

- An agent processing a request
- A tool being called
- A handoff between agents

These spans are processed by trace processors, which can store, display, or analyze the trace data.

## Configuring tracing

Tracing is enabled by default. You can configure it in the `config/agents.php` file:

```php
'tracing' => [
    'enabled' => true,
    'include_sensitive_data' => false,
],
```

You can also configure tracing for a specific run using `RunConfig`:

```php
use OpenAI\Agents\Facades\Runner;
use OpenAI\Agents\RunConfig;

$runConfig = new RunConfig();
$runConfig->withTracingDisabled(false)
    ->withTraceIncludeSensitiveData(true)
    ->withWorkflowName("Customer Support Workflow")
    ->withTraceId("trace-123")
    ->withGroupId("conversation-456")
    ->withTraceMetadata([
        'user_id' => 12345,
        'channel' => 'website',
    ]);

$result = Runner::runSync($agent, "Help me with my order", null, null, $runConfig);
```

## Tracing events

The tracing system records the following types of events:

1. **Trace start/end**: Recorded when a trace begins and ends
2. **Agent spans**: Recorded for each agent in the run
3. **Tool calls**: Recorded when a tool is called
4. **Handoffs**: Recorded when an agent hands off to another agent
5. **Errors**: Recorded when exceptions or errors occur

## Accessing trace data

By default, the trace data is logged using Laravel's logging system. You can access this data in your application's logs.

You can also create custom trace processors to handle trace data in different ways:

```php
use OpenAI\Agents\Tracing\TraceProcessorInterface;
use OpenAI\Agents\Tracing\TraceSpan;

class DatabaseTraceProcessor implements TraceProcessorInterface
{
    public function onTraceStart(string $traceId, string $workflowName, ?string $groupId, ?array $metadata): void
    {
        // Store trace start in database
        \App\Models\Trace::create([
            'trace_id' => $traceId,
            'workflow_name' => $workflowName,
            'group_id' => $groupId,
            'metadata' => json_encode($metadata),
            'start_time' => now(),
        ]);
    }
    
    public function onSpanStart(string $traceId, TraceSpan $span): void
    {
        // Store span start in database
        \App\Models\TraceSpan::create([
            'trace_id' => $traceId,
            'span_id' => $span->getId(),
            'name' => $span->getName(),
            'type' => $span->getType(),
            'attributes' => json_encode($span->getAttributes()),
            'start_time' => now(),
        ]);
    }
    
    public function onSpanError(string $traceId, TraceSpan $span, string $message, array $data): void
    {
        // Store span error in database
        \App\Models\TraceError::create([
            'trace_id' => $traceId,
            'span_id' => $span->getId(),
            'message' => $message,
            'data' => json_encode($data),
            'time' => now(),
        ]);
    }
    
    public function onSpanEnd(string $traceId, TraceSpan $span): void
    {
        // Update span in database with end time
        \App\Models\TraceSpan::where('span_id', $span->getId())
            ->update([
                'end_time' => now(),
                'duration' => $span->getDuration(),
            ]);
    }
    
    public function onTraceEnd(string $traceId, array $spans, int $duration): void
    {
        // Update trace in database with end time and duration
        \App\Models\Trace::where('trace_id', $traceId)
            ->update([
                'end_time' => now(),
                'duration' => $duration,
                'span_count' => count($spans),
            ]);
    }
}
```

To register your custom trace processor, you need to extend the package's service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI\Agents\Tracing\Trace;
use App\Services\DatabaseTraceProcessor;

class AgentsExtensionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->resolving(Trace::class, function ($trace) {
            $trace->addProcessor(new DatabaseTraceProcessor());
            return $trace;
        });
    }
}
```

## Visualizing traces

You can build a custom UI to visualize traces stored in your database. Here's a simple example of a Laravel controller and Blade view for displaying traces:

```php
namespace App\Http\Controllers;

use App\Models\Trace;
use App\Models\TraceSpan;
use Illuminate\Http\Request;

class TraceController extends Controller
{
    public function index()
    {
        $traces = Trace::orderBy('start_time', 'desc')
            ->paginate(15);
        
        return view('traces.index', compact('traces'));
    }
    
    public function show($traceId)
    {
        $trace = Trace::where('trace_id', $traceId)->firstOrFail();
        $spans = TraceSpan::where('trace_id', $traceId)
            ->orderBy('start_time')
            ->get();
        
        return view('traces.show', compact('trace', 'spans'));
    }
}
```

```blade
<!-- resources/views/traces/show.blade.php -->
<div class="trace-viewer">
    <h1>Trace: {{ $trace->workflow_name }}</h1>
    <p>ID: {{ $trace->trace_id }}</p>
    <p>Duration: {{ $trace->duration / 1000000 }} seconds</p>
    
    <h2>Spans</h2>
    <div class="timeline">
        @foreach($spans as $span)
        <div class="span" style="margin-left: {{ $span->start_time - $trace->start_time }}px; width: {{ $span->duration }}px;">
            <div class="span-header">{{ $span->name }} ({{ $span->type }})</div>
            <div class="span-body">
                <pre>{{ json_encode(json_decode($span->attributes), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endforeach
    </div>
</div>
```

## Using tracing for analytics

You can use trace data for analytics to better understand your agent workflows:

```php
namespace App\Services;

use App\Models\Trace;
use App\Models\TraceSpan;
use Illuminate\Support\Facades\DB;

class AgentAnalytics
{
    public function getAverageRunTime($workflowName, $timeframe = 'day')
    {
        return Trace::where('workflow_name', $workflowName)
            ->where('start_time', '>=', now()->sub($timeframe, 1))
            ->avg('duration');
    }
    
    public function getToolUsage($timeframe = 'day')
    {
        return TraceSpan::where('type', 'tool')
            ->where('start_time', '>=', now()->sub($timeframe, 1))
            ->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    public function getHandoffFrequency($timeframe = 'day')
    {
        return TraceSpan::where('type', 'handoff')
            ->where('start_time', '>=', now()->sub($timeframe, 1))
            ->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    public function getErrorRate($workflowName, $timeframe = 'day')
    {
        $totalRuns = Trace::where('workflow_name', $workflowName)
            ->where('start_time', '>=', now()->sub($timeframe, 1))
            ->count();
        
        $errorRuns = Trace::where('workflow_name', $workflowName)
            ->where('start_time', '>=', now()->sub($timeframe, 1))
            ->whereHas('errors')
            ->count();
        
        return $totalRuns > 0 ? ($errorRuns / $totalRuns) * 100 : 0;
    }
}
```

## Next steps

- Learn about [Context](context.md)
- Learn about [Results](results.md)
- Learn about [Streaming](streaming.md)