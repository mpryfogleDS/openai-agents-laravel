<?php

namespace OpenAI\Agents;

use Illuminate\Support\ServiceProvider;
use OpenAI\Agents\Models\ModelSettings;
use OpenAI\Agents\Models\OpenAIProvider;
use OpenAI\Agents\Models\ModelProviderInterface;

class AgentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/agents.php', 'agents'
        );

        $this->app->singleton(ModelProviderInterface::class, function ($app) {
            return new OpenAIProvider($app['config']['agents.api_key']);
        });

        $this->app->singleton(ModelSettings::class, function ($app) {
            return new ModelSettings(
                $app['config']['agents.model_settings.temperature'],
                $app['config']['agents.model_settings.top_p'],
                $app['config']['agents.model_settings.frequency_penalty'],
                $app['config']['agents.model_settings.presence_penalty'],
                $app['config']['agents.model_settings.max_tokens'],
                $app['config']['agents.model_settings.timeout']
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/agents.php' => config_path('agents.php'),
            ], 'agents-config');
        }
    }
}