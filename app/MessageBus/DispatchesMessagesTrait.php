<?php

namespace App\MessageBus;

trait DispatchesMessagesTrait
{
    protected function dispatchMessage(MessageInterface $message)
    {
        return app()->make(ApplicationMessageManager::class)->dispatch($message);
    }

    protected function queueMessage(MessageInterface $message)
    {
        return app()->make(ApplicationMessageManager::class)->queue($message);
    }
}