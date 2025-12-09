<?php

namespace Exowpee\LaravelFlow\Contracts;

use Exowpee\LaravelFlow\Context;
use Illuminate\Events\Dispatcher;

abstract class ModuleSubscriber
{
    /**
     * Définit le nom de module pour isoler les metadatas
     */
    final protected function getModuleName(): string
    {
        $class = static::class;
        $parts = explode('\\', $class);

        $vendor = strtolower($parts[0]);
        $packageName = strtolower($parts[1]);

        return "{$vendor}-{$packageName}";
    }

    /**
     * Définit le mapping des hooks vers leurs handlers.
     */
    abstract protected function hooks(): array;

    /**
     * Méthode appelée par Laravel pour enregistrer les listeners
     */
    final public function subscribe(Dispatcher $events): void
    {
        $moduleName = $this->getModuleName();

        foreach ($this->hooks() as $hook => $handlers) {
            foreach ($handlers as $handler) {
                $events->listen($hook, function (Context $context) use ($moduleName, $handler): void {
                    [$class, $method] = $handler;

                    try {
                        $context->_enterModuleContext($moduleName);

                        $instance = app($class);
                        $instance->$method($context);
                    } finally {
                        $context->_exitModuleContext();
                    }
                });
            }
        }
    }
}
