<?php

use Exowpee\LaravelFlow\Context;
use Exowpee\LaravelFlow\FlowManager;

if (!function_exists('flow')) {
    /**
     * Accès au FlowManager
     */
    function flow(): FlowManager
    {
        return app(FlowManager::class);
    }
}

if (!function_exists('ctx')) {
    /**
     * Manipulation du contexte Flow de manière fluide
     * 
     *   ctx()                           → Retourne le Context complet
     *
     *   ctx(['user' => $user])          → Set multiple (array style)
     *
     *   ctx(compact('user', 'order'))   → Set multiple via compact()
     */
    function ctx(?array $data = null): Context
    {
        $context = flow()->current();

        if ($data === null) {
            return $context;
        }

        foreach ($data as $key => $value) {
            $context->$key = $value;
        }

        return $context;
    }
}

if (!function_exists('gctx')) {
    /**
     * Récupère la valeur d'une clé dans le contexte courant
     */
    function gctx(string $key, mixed $default = null): mixed
    {
        return flow()->current()->$key ?? $default;
    }
}

if (!function_exists('hook')) {
    /**
     * Émet un événement flow avec le contexte courant
     * 
     *   hook('event-name')                          → Émet simplement l'événement
     * 
     *   hook('event-name', ['key' => 'value'])      → Set le contexte puis émet
     * 
     *   hook('event-name', compact('user', 'data')) → Set via compact() puis émet
     */
    function hook(string $event, ?array $context = null): void
    {
        if ($context !== null) {
            ctx($context);
        }

        flow()->hook($event);
    }
}
