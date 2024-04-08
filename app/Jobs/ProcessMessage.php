<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\MessageBus\MessageInterface;
use App\MessageBus\ApplicationMessageManager;

class ProcessMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function __construct(
        public MessageInterface $message,
    ) {}
    
    public function handle(ApplicationMessageManager $messageManager): void
    {
        try {
            $messageManager->dispatch($this->message);
        } catch (\DomainException $e) {
            $this->fail($e);
        }
    }
}
