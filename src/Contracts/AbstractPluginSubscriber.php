<?php

namespace Exowpee\LaravelFlow\Contracts;

use Illuminate\Events\Dispatcher;
use Exowpee\LaravelFlow\Context;

abstract class AbstractPluginSubscriber implements PluginSubscriberContract
{
    /**
     * Chaque subscriber doit fournir son nom de plugin
     */
    abstract public static function getPluginName(): string;

    /**
     * Mapping d’événements 
     */
    protected array $events = [];

    /**
     * Méthode appelée par Laravel pour enregistrer les listeners
     */
    final public function subscribe(Dispatcher $events): void
    {
        foreach ($this->events as $event => $handlers) {
            foreach ($handlers as $handler) {
                $events->listen($event, function (Context $context) use ($handler) {
                    $this->handleWrapped($context, $handler[0], $handler[1]);
                });
            }
        }
    }

    /**
     * Enveloppe qui gère le contexte plugin
     */
    private function handleWrapped(Context $context, string $class, string $method): void
    {
        $pluginName = static::getPluginName();

        try {
            $context->_enterPluginContext($pluginName);

            $instance = app($class);
            $instance->$method($context);
        } finally {
            $context->_exitPluginContext();
        }
    }
}
