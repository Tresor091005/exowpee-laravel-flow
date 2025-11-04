<?php

namespace Exowpee\LaravelFlow\Contracts;

use Illuminate\Events\Dispatcher;

interface PluginSubscriberContract
{
    /**
     * Nom unique du plugin (utilisé pour les métadonnées, le contexte, etc.)
     */
    public static function getPluginName(): string;

    /**
     * Méthode standard Laravel pour enregistrer les événements
     */
    public function subscribe(Dispatcher $events): void;
}
