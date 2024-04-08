<?php

namespace App\MessageBus;

final class ApplicationMessageManager
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private MessageBusInterface $queueBus,
    ) {}
    
    public function dispatch(MessageInterface $message)
    {
        if ($message instanceof CommandInterface) {
            $bus = $this->commandBus;
        } elseif ($message instanceof QueryInterface) {
            $bus = $this->queryBus;
        } else {
            throw new \LogicException('Unknown message type');
        }
        
        if (!$bus->canDispatch($message)) {
            throw new \LogicException('Message can\'t be dispatched. There is no appropriate handler');
        }
        
        return $bus->dispatch($message);
    }
    
    public function queue(MessageInterface$message)
    {
        if ($message instanceof CommandInterface) {
            $bus = $this->commandBus;
        } elseif ($message instanceof QueryInterface) {
            $bus = $this->queryBus;
        } else {
            throw new \LogicException('Unknown message type');
        }
        
        if (!$bus->canDispatch($message)) {
            throw new \LogicException('Message can\'t be queued. There is no appropriate handler');
        }
        
        return $this->queueBus->dispatch($message);
    }
}
