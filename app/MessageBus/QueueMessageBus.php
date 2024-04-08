<?php

namespace App\MessageBus;

use App\Jobs;

class QueueMessageBus implements MessageBusInterface
{
    #[\Override]
    public function canDispatch(MessageInterface $message): bool
    {
        return true;
    }
    
    #[\Override]
    public function dispatch(MessageInterface $message)
    {
        if ($message instanceof CommandInterface) {
            $queue = 'commands';
        } elseif ($message instanceof QueryInterface) {
            $queue = 'queries';
        } else {
            throw new \LogicException('Unknown message type');
        }
        
        Jobs\ProcessMessage::dispatch($message)->onQueue($queue);
    }
}
