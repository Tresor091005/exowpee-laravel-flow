<?php

use Exowpee\LaravelFlow\FlowManager;

if (!function_exists('flow')) {
    /**
     * Utilitaire de manipulation du FlowManager
     */
    function flow(): FlowManager
    {
        return app(FlowManager::class);
    }
}