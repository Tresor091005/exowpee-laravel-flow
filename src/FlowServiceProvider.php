<?php

namespace Exowpee\LaravelFlow;

use Illuminate\Support\ServiceProvider;

class FlowServiceProvider extends ServiceProvider
{
    /**
     * Register the services of the package in the IOC container.
     */
    public function register(): void
    {
        $this->app->scoped(FlowManager::class, function ($app) {
            return new FlowManager();
        });
        $this->app->alias(FlowManager::class, 'flow');
    }

    /**
     * Start the services after all providers have been registered.
     */
    public function boot(): void
    {

    }
}