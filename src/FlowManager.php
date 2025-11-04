<?php

namespace Exowpee\LaravelFlow;

use Exowpee\LaravelFlow\Exceptions\FlowException;

class FlowManager
{
    private const ATTRIBUTE_KEY = '__flow_context';
    
    /**
     * Démarre un nouveau flow avec un Context initial
     * 
     * Stocke le Context dans request()->attributes pour qu'il soit
     * accessible partout pendant le cycle de vie de la requête
     */
    public function start(array $data = []): Context
    {
        $context = $this->current();
        
        if ($context) {
            throw new FlowException(
                "Cannot start a new context: there is already an active context. " .
                "Call flow()->stop() first."
            );
        }

        $context = new Context($data);
        request()->attributes->set(self::ATTRIBUTE_KEY, $context);
        
        return $context;
    }
    
    /**
     * Récupère le Context courant depuis la requête
     */
    public function current(): ?Context
    {
        return request()->attributes->get(self::ATTRIBUTE_KEY);
    }
    
    /**
     * Émet un event Laravel classique avec le Context courant
     * 
     * Les listeners reçoivent directement l'objet Context
     */
    public function emit(string $eventName): Context
    {
        $context = $this->current();
        
        if (!$context) {
            throw new FlowException(
                "No active context. Call flow()->start() first."
            );
        }
        
        // Dispatch l'event avec le context
        event($eventName, $context);
        
        return $context;
    }
    
    /**
     * Termine le flow et nettoie le Context
     * 
     * @return Context Le context final pour inspection
     */
    public function stop(): Context
    {
        $context = $this->current();
        
        if (!$context) {
            throw new FlowException(
                "No active context to stop. Call flow()->start() first."
            );
        }
        
        // Cleanup
        request()->attributes->remove(self::ATTRIBUTE_KEY);
        
        return $context;
    }
}