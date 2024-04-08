<?php

namespace App\MessageBus;

trait MessageBusAwareProvider
{
    public function bootMessageBuses(): void
    {
        $this->bootCommandBus();
        $this->bootQueryBus();
    }
    
    private function bootCommandBus(): void
    {
        if (!property_exists($this, 'commandsMapping')) {
            return;
        }
        
        $commandBus = $this->app->make('CommandBus');
        $mapping = $this->commandsMapping ?? [];
        foreach ($mapping as $commandClass => $handler) {
            $commandBus->addHandler($commandClass, $handler);
            
        }
    }
    
    private function bootQueryBus(): void
    {
        if (!property_exists($this, 'queriesMapping')) {
            return;
        }
        
        $queryBus = $this->app->make('QueryBus');
        $mapping = $this->queriesMapping ?? [];
        foreach ($mapping as $queryClass => $handler) {
            $queryBus->addHandler($queryClass, $handler);
        }
    }
}

