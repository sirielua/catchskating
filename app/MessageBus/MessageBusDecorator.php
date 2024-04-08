<?php

namespace App\MessageBus;

abstract class MessageBusDecorator implements MessageBusInterface
{
    protected $bus;
    
    public function __construct(MessageBusInterface $decorated)
    {
        $this->bus = $decorated;
    }
    
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->bus, $name], $arguments);
    }
    
    public function __get($name)
    {
        return $this->bus->$name;
    }
    
    public function __set($name, $value)
    {
        $this->bus->$name = $value;
    }
    
    #[\Override]
    public function canDispatch($message): bool
    {
        return $this->bus->canDispatch($message);
    }
    
    #[\Override]
    public function dispatch($message)
    {
        return $this->bus->dispatch($message);
    }
}
