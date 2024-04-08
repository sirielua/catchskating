<?php

namespace App\MessageBus;

interface MessageBusInterface
{
    public function canDispatch(MessageInterface $message): bool;
    
    public function dispatch(MessageInterface $message);
}
