<?php

namespace App\MessageBus;

use Psr\Container\ContainerInterface;

class MessageBus implements MessageBusInterface
{
    public function __construct(
        private ContainerInterface $container,
        private array $handlers = [],
    ) {}
    
    public function addHandler(string $messageClass, string|callable $handlers): void
    {
        $this->handlers[$messageClass] = $handlers;
    }
    
    #[\Override]
    public function canDispatch(object $message): bool
    {
        $messageClass = get_class($message);
        return isset($this->handlers[$messageClass]);
    }
    
    #[\Override]
    public function dispatch($message)
    {
        if (!$this->canDispatch($message)) {
            throw new \LogicException(sprintf('Message %s can\'t be dispatched (Handler is not registered)', get_class($message)));
        }
        $handler = $this->handlers[get_class($message)];
        return $this->resolveHandler($handler, $message, $this->container);
    }
    
    private function resolveHandler($handler, object $message, ContainerInterface $container)
    {
        if (is_string($handler)) {
            $handler = $this->resolveStringHandler($handler, $container);
        }

        if (!is_callable($handler)) {
            throw new \LogicException('$handler is not resolvable'); 
        }
        
        return call_user_func($handler, $message);
    }
    
    private function resolveStringHandler(string $handler, ContainerInterface $container)
    {
        if (false !== strpos($handler, '@')) {
            return function ($message) use ($handler, $container) {
                list($class, $method) = explode('@', $handler, 2);
                return $container->get($class)->$method($message);
            };
        }
        
        return $container->get($handler);
    }
}
